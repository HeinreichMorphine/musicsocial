# Reso – Architecture Overview for Mock Presentation

This document condenses your project's technical architecture into a clear, presentation-ready summary. It highlights the **end‑to‑end data flow**, the **recommendation engine**, and the **post composer** — a key interaction point that drives both social engagement and taste profiling.

---

## 1. System Topology & Core Data Models

The system is built on **Laravel 10** (PHP) with a **MySQL** database, complemented by a **Python/Flask** microservice for recommendation logic.

**Central Models**:

| Model | Purpose |
|-------|---------|
| **User** | Authentication, Spotify OAuth tokens, onboarding status. |
| **Song** | Metadata authority: Spotify ID, YouTube ID, title, artist, album art, and a JSON `genres` vector (enriched from multiple APIs). |
| **Share** | User post sharing a song with a caption and type (`music`, `text`, `recommendation_request`). |
| **SongInteraction** | Logs user actions (`listen`, `like`, `dislike`, `share`) – the training signal for the recommender. |
| **UserShelfSong** | 5 curated songs selected during onboarding – prevents cold-start. |
| **Playlist** / **PlaylistSong** / **PlaylistCollaborator** | Shared, collaborative playlists with participant permissions. |
| **Comment** & **CommentThread** | Threaded comments with song URL auto-detection. |

---

## 2. End‑to‑End Data Flow (Ingress → Intelligence → Egress)

### Phase 1: Metadata Ingress & Enrichment  
When a user shares a song, the backend gathers metadata from multiple sources to build a rich genre vector.

**Key code** (`ShareController`):
```php
// 1. Fetch Spotify track details
$trackData = $this->spotifyService->getTrack($validated['spotify_track_id']);
$genres = json_decode($song->genres, true) ?? [];

// 2. Enrich with MusicBrainz artist genres
$musicBrainzGenres = $this->musicBrainzService->getArtistGenres($song->artist_name);
$genres = array_unique(array_merge($genres, $musicBrainzGenres));

// 3. Enrich with Discogs track styles
$discogsGenres = $this->discogsService->getGenres($song->artist_name, $song->track_name);
$genres = array_unique(array_merge($genres, $discogsGenres));

// 4. Fallback to YouTube tags if genres are still empty
if (empty($genres) && !empty($song->youtube_video_id)) {
    $videoData = $this->youTubeService->getVideo($song->youtube_video_id);
    $youtubeGenres = $this->extractGenresFromText($videoData['title'] . ' ' . implode(' ', $videoData['tags'] ?? []));
    $genres = array_unique(array_merge($genres, $youtubeGenres));
}

// 5. Save the enriched vector
$song->update(['genres' => json_encode(array_values(array_unique($genres)))]);
```

**Why it matters**: The multi‑source enrichment produces a high‑quality keyword vector for content‑based similarity, feeding directly into the recommendation engine.

---

### Phase 2: User Engagement (The Feedback Loop)  
Likes and dislikes are mutually exclusive – toggling a like removes any existing dislike, ensuring clean training data.

**`LikeController` toggle logic**:
```php
public function toggle(Share $share) {
    $user = auth()->user();

    // Mutual exclusivity: remove dislike if present
    if ($user->dislikes->contains($share)) {
        $user->dislikes()->detach($share);
    }
    $user->likes()->toggle($share);

    return response()->json([...]);
}
```

**Comment auto‑detection**: If a comment contains a Spotify track URL, the backend fetches its metadata and appends a hidden `[SONG:spotify_id]` tag. The frontend renders a play card dynamically using Alpine.js – turning comments into rich music recommendations.

---

### Phase 3: Intelligence Processing (Recommender Microservice)  
Laravel calls the Flask service (`RecommendationService`) which decides which algorithm to apply based on the user’s interaction count.

**Decision thresholds**:
- **HOT** (Collaborative SVD): `effective_interactions >= 10`
- **WARM** (TF‑IDF Cosine Similarity): `5 <= effective_interactions < 10` – *all new users start here because onboarding forces 5 shelf songs.*
- **COLD** (Popularity fallback): only a safety net, normally unreachable.

**Final recommendation score** fuses algorithmic prediction with social trust:

`Total Score = (Base Score × 0.7) + (Social Trust Boost × 0.3)`

**SVD with context boost** (Python):
```python
pred = algo.predict(user_id, song_id)
current_score = pred.est

# +0.4 if the artist is a known favorite
if artist in liked_artists:
    current_score += 0.4
```

**TF‑IDF warm‑start** (Python):
```python
user_profile = tfidf.transform(user_liked_songs_df['features']).mean(axis=0)
similarities = cosine_similarity(user_profile, all_features_matrix)[0]
```

**Social Trust** – dampened follower influence:
```python
trust = math.log(1 + pow(sharer_friends, 0.7)) / (1 + 0.5 * math.log(1 + active_user_friends))
```
- Relationship multiplier: `1.0` for playlist peers, `0.8` for followed friends, `0.3` for strangers.

---

### Phase 4: Data Egress (Hydration & Display)  
The `DiscoveryController` fetches recommendations, filters out already-interacted songs, hydrates the top 12 results with database metadata, sorts them back into recommended order, and determines "Who to Follow" using a taste-neighbor query.

**Key Recommendation Hydration & Ordering** (`DiscoveryController@index`):
```php
// 1. Fetch raw recommendations from the recommender microservice
$rawRecommendations = $this->recommendationService->getRecommendations($user->id);
$recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();

// 2. Filter out songs user has interacted with (Listened/Liked/Disliked)
$interactedSongIds = \App\Models\SongInteraction::where('user_id', $user->id)
                        ->pluck('song_id')
                        ->toArray();
$filteredSongIds = array_diff($recommendedSongIds, $interactedSongIds);

// 3. Take top 12 valid recommendation IDs (preserving recommender sorting)
$top12Ids = array_slice($filteredSongIds, 0, 12);
$recommendationData = collect($rawRecommendations)->keyBy('song_id');

// 4. Hydrate Song models from DB
$recommendedSongs = Song::whereIn('id', $top12Ids)->get();

// 5. Re-sort songs by recommendation score (whereIn does not preserve order)
$recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
    return $recommendationData[$song->id]['score'] ?? 0;
})->values();

// 6. Map scores, reasons, and debug logs into the model properties
$recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
    $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
    $song->score = $recommendationData[$song->id]['score'] ?? null;
    $song->algo_debug = $recommendationData[$song->id]['debug'] ?? null;
    return $song;
});
```

**"Who to Follow" (Taste Neighbors) Query**:
To boost community engagement, the discovery engine identifies "Taste Neighbors" (users who have liked the same songs) and suggests following them:
```php
// 1. Find users who liked the same songs as the current user (Taste Neighbors)
$likedSongIds = $user->likes->pluck('song.id');
$tasteNeighbors = User::where('id', '!=', $user->id)
    ->whereHas('likes', function ($query) use ($likedSongIds) {
        $query->whereIn('song_id', $likedSongIds);
    })
    ->whereDoesntHave('followers', function ($query) use ($user) {
        $query->where('follower_id', $user->id);
    })
    ->withCount('followers')
    ->orderByDesc('followers_count') // Prioritize more popular users among taste neighbors
    ->limit(3)
    ->get();

// 2. Fill remaining suggestion slots (up to 5) with other active users
$otherUsers = User::where('id', '!=', $user->id)
    ->whereNotIn('id', $tasteNeighbors->pluck('id'))
    ->whereDoesntHave('followers', function ($query) use ($user) {
        $query->where('follower_id', $user->id);
    })
    ->inRandomOrder()
    ->limit(5 - $tasteNeighbors->count())
    ->get();

$usersToSuggest = $tasteNeighbors->merge($otherUsers);
```

**How the "Who to Follow" Algorithm Works**:
To drive social network density and community interactions, the system suggests a personalized pool of **5 users** through a two-tiered selection query:
* **Tier 1: Taste Neighbors (High-Relevance affinity, capped at 3)**:
  * Looks up all songs the active user has liked.
  * Queries for other users who have liked at least one of those same songs (co-likes).
  * Excludes the active user themselves and users they already follow.
  * Prioritizes influential users by sorting them by follower count descending.
* **Tier 2: Explorer Fallback (Fills the remaining slots up to 5)**:
  * If the active user has fewer than 3 Taste Neighbors, the system pulls random active users to fill the remaining slots.
  * Ensures that a new user (cold start) or user with niche preferences always sees a complete set of 5 suggestions without database overlap.

The results are passed to the `discovery.blade.php` view, displaying the recommendation list and custom-tailored user connection widgets.

---

## 3. Post Composer – The Heart of Social Sharing

The composer is an Alpine.js‑powered component that allows users to search for a track, select it, add a caption, and post it to their feed. Each post strengthens the user’s taste profile.

### Key Functions

**`search()`** – queries Spotify API when the user types ≥ 3 characters:
```javascript
search() {
    if (this.searchQuery.length < 3) { this.searchResults = []; return; }
    this.loading = true;
    fetch(`/spotify/search?query=${encodeURIComponent(this.searchQuery)}`)
        .then(response => response.json())
        .then(data => { this.searchResults = data; this.loading = false; });
}
```

**`selectTrack(track)`** – stores the chosen track and clears the search results:
```javascript
selectTrack(track) {
    this.selectedTrack = track;
    this.searchQuery = '';
    this.searchResults = [];
}
```

**`submitPost()`** – sends a POST request to `/shares/store` with the track ID, caption, and post type (music or recommendation_request). The response HTML (the new share card) is prepended to the feed container without a page reload.

```javascript
submitPost() {
    if (!this.selectedTrack) return;
    this.loading = true;

    const formData = new FormData();
    formData.append('type', this.isSeekingRecommendations ? 'recommendation_request' : 'music');
    formData.append('spotify_track_id', this.selectedTrack.id);
    formData.append('caption', this.$refs.captionInput.value);

    fetch('/shares/store', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            document.getElementById('feed-container').insertAdjacentHTML('afterbegin', data.html);
            this.resetComposer();
        });
}
```

**UX highlights**:
- **Recently played** list appears when the input is focused (fetched via `/spotify/recently-played`).
- A toggle switches between **“Just Sharing”** and **“Asking for Recommendations”** – the latter changes the post type and encourages community replies.
- A first‑time tip reminds users that posting songs refines their taste profile.

---

## 4. Persistent Spotify Web Player

The application embeds the Spotify Web Playback SDK in `app.blade.php`. The `spotify-web-player` Alpine component maintains a persistent player that survives page navigation.

**Key mechanism**:
- For **Premium users**, the SDK connects to a virtual device.
- For **free users**, native HTML5 audio previews are used, with event listeners for time and ended events.
- A global `window.toggleSpotifyPlayer()` function is exposed so any share card or discovery card can trigger playback, waking up the persistent player UI.

---

## 5. Routing & Page Map (Quick Reference)

| Page | Route | Controller | View |
|------|-------|------------|------|
| Home Feed | `/dashboard` | `FeedController@index` | `dashboard.blade.php` |
| Discovery | `/discovery` | `DiscoveryController@index` | `discovery.blade.php` |
| User Profile | `/users/{user:name}` | `UserProfileController@show` | `profile/show.blade.php` |
| Playlists | `/playlists` | `PlaylistController@index` | `playlists/index.blade.php` |
| Onboarding | `/onboarding/genres` | `OnboardingController@genres` | `onboarding/genres.blade.php` |
| Admin Retrain | `/admin/retrain` | `AdminController@retrainPage` | `admin/retrain.blade.php` |

---

## 6. Complete Directory Structure & Core Code Map

To navigate the overall project, here is the complete folder structure mapping all core modules, routing systems, and view templates across both the Laravel and Python codebases:

```
reso/ (Root Directory)
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Laravel Web and AJAX Controllers
│   │   │   ├── AdminController.php
│   │   │   ├── CommentController.php
│   │   │   ├── DiscoveryController.php
│   │   │   ├── LikeController.php
│   │   │   ├── OnboardingController.php
│   │   │   ├── ShareController.php
│   │   │   └── ... (Other Controllers)
│   │   └── Middleware/           # Routing middleware (auth, admin blocks)
│   ├── Models/                   # Laravel Eloquent Database Models
│   │   ├── Admin.php
│   │   ├── Comment.php
│   │   ├── CommentThread.php
│   │   ├── Playlist.php
│   │   ├── PlaylistCollaborator.php
│   │   ├── PlaylistSong.php
│   │   ├── Share.php
│   │   ├── Song.php
│   │   ├── SongInteraction.php
│   │   ├── User.php
│   │   └── UserShelfSong.php
│   └── Services/                 # Business logic and external API wrappers
│       ├── DiscogsService.php
│       ├── GenreCleanerService.php
│       ├── MusicBrainzService.php
│       ├── RecommendationService.php # Connects Laravel to Flask Recommender
│       ├── SpotifyService.php
│       └── YouTubeService.php
├── bootstrap/                    # Laravel application bootstrap configuration
├── config/                       # Application configuration values (database, services)
├── database/
│   ├── migrations/               # MySQL database schema definition files
│   └── seeders/                  # Mock data generators for local deployment
├── public/                       # Entry point (index.php) and compiled web assets
├── recommender_service/          # Python/Flask Machine Learning Microservice
│   ├── app.py                    # Recommender API service, SVD & TF-IDF engines
│   ├── benchmark_model.py        # 5-fold cross validation script
│   ├── test_recommender.py       # Python recommender unit test suite
│   ├── recs.json                 # Cached SVD recommendations database
│   └── requirements.txt          # Python dependency list (scikit-surprise, pandas, etc.)
├── resources/
│   ├── css/                      # Application custom stylesheets (Vanilla CSS)
│   ├── js/                       # Alpine.js script components
│   └── views/                    # Blade Templates (HTML & Tailwind/Alpine directives)
│       ├── admin/                # Admin dashboards and model controls
│       ├── components/           # Reusable UI widgets (cards, modals, player)
│       ├── layouts/              # Main layout wrappers (app.blade.php)
│       ├── onboarding/           # Onboarding view templates
│       ├── playlists/            # Playlist index and detail views
│       └── discovery.blade.php   # Main personalization and social suggestions view
├── routes/
│   ├── api.php                   # Public / mobile API routes
│   ├── auth.php                  # Authentication and password recovery routes
│   └── web.php                   # Core application web routes (controller maps)
└── tests/                        # Laravel Feature and Unit Testing framework
```

---

## 7. Key Architecture File Reference Table

For a deep dive into specific architectural elements, refer to these primary files in the repository:

| System Layer | Component / File | Purpose |
|--------------|-------------------|---------|
| **Recommendation Engine** | [`recommender_service/app.py`](file:///C:/laragon/www/musicsocial-main/recommender_service/app.py) | Contains SVD training, TF-IDF calculation, cosine similarity matrix, and Flask API endpoints. |
| **Recommendation Client** | [`app/Services/RecommendationService.php`](file:///C:/laragon/www/musicsocial-main/app/Services/RecommendationService.php) | Wrapper that communicates with the Flask microservice with built-in retry and timeout logic. |
| **Discovery Controller** | [`app/Http/Controllers/DiscoveryController.php`](file:///C:/laragon/www/musicsocial-main/app/Http/Controllers/DiscoveryController.php) | Hydrates recommendations, filters out user-interacted tracks, and runs the "Taste Neighbors" follow suggestion algorithm. |
| **Discovery View** | [`resources/views/discovery.blade.php`](file:///C:/laragon/www/musicsocial-main/resources/views/discovery.blade.php) | Main interface rendering Alpine.js discovery tabs, Spotify play cards, and follow components. |
| **Data Ingress & Enrichment** | [`app/Http/Controllers/ShareController.php`](file:///C:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php) | Coordinates the multi-source enrichment pipeline (Spotify, MusicBrainz, Discogs, YouTube tags). |
| **Enrichment Services** | [`app/Services/`](file:///C:/laragon/www/musicsocial-main/app/Services/) | Custom wrappers calling third-party APIs: `SpotifyService.php`, `MusicBrainzService.php`, `DiscogsService.php`, and `YouTubeService.php`. |
| **Engagement Handlers** | [`app/Http/Controllers/LikeController.php`](file:///C:/laragon/www/musicsocial-main/app/Http/Controllers/LikeController.php) | Handles likes/dislikes with mutual exclusivity logic. |
| | [`app/Http/Controllers/CommentController.php`](file:///C:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php) | Implements comment storage and Spotify track link auto-detection tags (`[SONG:spotify_id]`). |
| **Persistent Player SDK** | [`resources/views/components/spotify-web-player.blade.php`](file:///C:/laragon/www/musicsocial-main/resources/views/components/spotify-web-player.blade.php) | Manages the Spotify Web Playback SDK connection, virtual device initialization, audio playback state, and exposes global trigger. |
| **Onboarding Pipeline** | [`app/Http/Controllers/OnboardingController.php`](file:///C:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php) | Manages step-by-step onboarding, genre selection, and the cold-start shelf songs creation. |
| **Model Retrain Endpoint** | [`app/Http/Controllers/AdminController.php`](file:///C:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php) | Handles the admin action to manually trigger recommender retraining. |

---

## 8. RecSys Test Suite & Simulation (Verification Framework)

To guarantee the reliability and math accuracy of the Python microservice, the system includes a dedicated unit test suite ([`recommender_service/test_recommender.py`](file:///C:/laragon/www/musicsocial-main/recommender_service/test_recommender.py)).

### Mock Ingress: How the Test Suite Fetches Data
To run tests without requiring a running MySQL database, the test suite intercepts Pandas SQL calls and SQLAlchemy connection blocks.

1. **`pandas.read_sql` Interception (`@patch`)**:
   The test suite patches SQL routing with a custom side-effect parser:
   ```python
   @patch('app.pd.read_sql')
   def test_cold_start_content_based(self, mock_read_sql):
       # Define query routing for mock_read_sql
       def mock_read_sql_side_effect(query, connection, params=None):
           normalized_query = " ".join(str(query).lower().split())
           
           if "from songs" in normalized_query:
               return all_songs_df  # Pre-defined test catalog
           elif "from likes l join shares s" in normalized_query and "select s.song_id" in normalized_query:
               return user_interactions_df  # Empty/populated interaction histories
           elif "from followers" in normalized_query:
               return followers_df  # Custom user follower numbers
           return pd.DataFrame()

       mock_read_sql.side_effect = mock_read_sql_side_effect
   ```

2. **Connection Context Mocking**:
   It mocks the SQLAlchemy execution pipeline to return deterministic values for row queries:
   ```python
   mock_conn = MagicMock()
   self.mock_engine.connect.return_value.__enter__.return_value = mock_conn

   def mock_execute_side_effect(query, *args, **kwargs):
       if "count(*) as cnt" in str(query).lower():
           return MockQueryResult([(5,)]) # Simulates onboarding 5-song warm-start threshold
       return MockQueryResult([])

   mock_conn.execute.side_effect = mock_execute_side_effect
   ```

### Core Verified Mathematical Models
- **TF-IDF Keyword Weighting**: Proves that rare genres (e.g., *Math-Rock*) are correctly weighted higher than dominant ones (e.g., *Pop*) based on logarithmic inverse document frequency.
- **Cosine Similarity Thresholding**: Assures that only songs exceeding the `> 0.1` similarity threshold are recommended.
- **Logarithmic Activity Flattening**: Verifies the SVD confidence scale weighting: $c_{ui} = 1 + \ln(1 + r_{ui})$.
- **Social Trust Boost dampening**: Checks follower trust value calculations: $\text{trust} = \frac{\ln(1 + F_{\text{sharer}}^{0.7})}{1 + 0.5 \ln(1 + F_{\text{active}})}$ combined with user relationship multipliers (Collaborators = `1.0`, Friends = `0.8`, Strangers = `0.3`).
- **Strict Benchmarking Standards**: Asserts model quality targets using live cross-validation (Root Mean Squared Error $\text{RMSE} < 1.0$, Mean Absolute Error $\text{MAE} < 0.85$, and Normalized Discounted Cumulative Gain $\text{NDCG} > 0.70$).

---

## In a Nutshell (for your examiner)

1. **Tech Stack**: Laravel + MySQL (core) + Python/Flask (ML microservice) + Spotify APIs.
2. **Data Pipeline**: Ingest via multiple APIs → enrich genres → store → user interactions → train/update models → serve personalized feeds.
3. **Recommendation**: Hybrid SVD + TF‑IDF, boosted by social trust – bypasses cold-start via mandatory onboarding shelf.
4. **Engagement**: Post composer is the primary interaction point; it fuels the feedback loop and integrates seamlessly with the persistent player.
5. **Real-time UX**: Alpine.js for reactivity, SPAs with persistent player, and AJAX submissions.

---

*This summary should give your examiner a clear picture of the system’s intelligence, social layers, and technical depth. Good luck with your mock presentation!*
