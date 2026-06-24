# Technical Architecture Overview

## 1. System Topology & Data Layer

The application utilizes a MySQL database to track social actions and user engagement. Relationships are structured around the central [Song](file:///c:/laragon/www/musicsocial-main/app/Models/Song.php) and [User](file:///c:/laragon/www/musicsocial-main/app/Models/User.php) models.

### Key Entities
1. **[User](file:///c:/laragon/www/musicsocial-main/app/Models/User.php)**: Stores authentication credentials, onboarding progress (`is_onboarded`), and Spotify OAuth tokens (`spotify_token`, `spotify_refresh_token`).
2. **[Song](file:///c:/laragon/www/musicsocial-main/app/Models/Song.php)**: The metadata authority. Stores identifiers (`spotify_track_id`, `youtube_video_id`), details (`track_name`, `artist_name`, `album_art_url`), and an enriched JSON array of keywords (`genres`).
3. **[Share](file:///c:/laragon/www/musicsocial-main/app/Models/Share.php)**: User-created social posts sharing a song with a caption and type (`music`, `text`, or `recommendation_request`).
4. **[Comment](file:///c:/laragon/www/musicsocial-main/app/Models/Comment.php) & [CommentThread](file:///c:/laragon/www/musicsocial-main/app/Models/CommentThread.php)**: Threaded comments supporting replies and user mentions.
5. **[SongInteraction](file:///c:/laragon/www/musicsocial-main/app/Models/SongInteraction.php)**: Log of user-song actions used directly as training algorithm weight inputs: `listen`, `like`, `dislike`, `share`.
6. **[UserShelfSong](file:///c:/laragon/www/musicsocial-main/app/Models/UserShelfSong.php)**: Stores 5 curated songs selected by the user during onboarding to prevent the cold-start problem.
7. **[Playlist](file:///c:/laragon/www/musicsocial-main/app/Models/Playlist.php) / [PlaylistCollaborator](file:///c:/laragon/www/musicsocial-main/app/Models/PlaylistCollaborator.php) / [PlaylistSong](file:///c:/laragon/www/musicsocial-main/app/Models/PlaylistSong.php)**: Manages shared, collaborative playlist structures, participant permissions, and song items.

---

## 2. End-to-End Data Flow: From Ingress to Intelligence

The data lifecycle follows a linear path: **Ingress (Sharing)** $\rightarrow$ **Engagement (Interactions)** $\rightarrow$ **Intelligence (Processing)** $\rightarrow$ **Egress (Discovery)**.

### Phase 1: Metadata Ingress & Enrichment
When a song is shared, the server performs multi-source API metadata gathering to construct a rich, search-optimized genre/tag vector.

*   **Primary Page/Form View**: [create.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/shares/create.blade.php)

#### Code Block: [ShareController.php (Metadata Enrichment)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php#L68-L112)
```php
// 1. Fetch Spotify track details
$trackData = $this->spotifyService->getTrack($validated['spotify_track_id']);
$song = $trackData['song'];
$genres = json_decode($song->genres, true) ?? [];

// 2. Enhance with MusicBrainz artist genres
$musicBrainzGenres = $this->musicBrainzService->getArtistGenres($song->artist_name);
if ($musicBrainzGenres && !isset($musicBrainzGenres['error'])) {
    $genres = array_unique(array_merge($genres, $musicBrainzGenres));
}

// 3. Enhance with Discogs track styles
$discogsGenres = $this->discogsService->getGenres($song->artist_name, $song->track_name);
if (!empty($discogsGenres)) {
    $genres = array_unique(array_merge($genres, $discogsGenres));
}

// 4. Fallback to YouTube tags if genres array is empty
if (empty($genres) && !empty($song->youtube_video_id)) {
    $videoData = $this->youTubeService->getVideo($song->youtube_video_id);
    if ($videoData) {
        $youtubeGenres = $this->extractGenresFromText($videoData['title'] . ' ' . implode(' ', $videoData['tags'] ?? []));
        $genres = array_unique(array_merge($genres, $youtubeGenres));
    }
}

// 5. Save the enriched genre/keyword vector
$song->update(['genres' => json_encode(array_values(array_unique($genres)))]);
```

##### How It Works (Sharing & Metadata Ingress):
1. **Fetch Spotify Core**: Queries the Spotify Web API to extract initial track details and basic artist information.
2. **MusicBrainz & Discogs Queries**: Enriches this baseline metadata by fetching the artist's historical genres and track-specific styles.
3. **YouTube Tags Fallback**: If the genre list remains empty, it queries YouTube's metadata to extract keywords from video tags/descriptions.
4. **Keyword Vector Update**: Merges, deduplicates, and saves the final list to the `genres` JSON column of the [Song Model](file:///c:/laragon/www/musicsocial-main/app/Models/Song.php) to prepare for similarity matching.

### Phase 2: User Engagement (The Feedback Loop)
User interactions produce training signals. The system implements mutual exclusivity to guarantee clean ratings (e.g., liking a share detaches any existing dislikes).

*   **Primary Feed Page**: [dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php)
*   **UI Components**:
    *   [share-card.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/share-card.blade.php)
    *   [post-composer.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/post-composer.blade.php)
    *   [comment.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/comment.blade.php)

#### Mutual Exclusivity Code: [LikeController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/LikeController.php#L23-L46)
```php
public function toggle(Share $share)
{
    $user = auth()->user();

    // 1. Enforce mutual exclusivity (unlike/dislike balance)
    if ($user->dislikes->contains($share)) {
        $user->dislikes()->detach($share);
    }

    // 2. Toggle the user's like state
    $user->likes()->toggle($share);

    return response()->json([
        'liked' => $user->likes->contains($share),
        'likesCount' => $share->likes()->count(),
        'disliked' => $user->dislikes->contains($share),
        'dislikesCount' => $share->dislikes()->count(),
    ]);
}
```

##### How It Works (Likes/Dislikes Mutual Exclusivity):
1. **Exclusivity Check**: Checks if the user dislikes the share. If they do, the dislike is detached before applying the like.
2. **Toggle Like**: Laravel toggles the user's like state on the share.
3. **JSON Response**: Returns the updated counts and user status to trigger the instant Alpine.js frontend UI change on the share card.

#### Spotify Link Auto-Detection Code: [CommentController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L57-L76)
```php
// Auto-detect a Spotify URL in the comment body and resolve its metadata
$songId = null;
if (preg_match('/https:\/\/open\.spotify\.com\/track\/([a-zA-Z0-9]+)/', $validated['body'], $trackMatches)) {
    $spotifyTrackId = $trackMatches[1];
    $trackData = $this->spotifyService->getTrack($spotifyTrackId);
    if (!isset($trackData['error']) && isset($trackData['song'])) {
        $songId = $trackData['song']->id;
    }
}

$body = $validated['body'];
if ($songId) {
    $song = \App\Models\Song::find($songId);
    if ($song && strpos($body, "[SONG:{$song->spotify_track_id}]") === false) {
         $body .= " [SONG:{$song->spotify_track_id}]";
    }
}
```

##### How It Works (Pasting a Song URL in Comments):
1. **URL Intercept**: When a user inputs a comment containing a Spotify track link, the backend regex detects it and queries Spotify's API to fetch the song metadata, saving it to our local database.
2. **Hidden Tag Generation**: It appends a hidden tag `[SONG:spotify_track_id]` to the end of the text.
3. **Clean Presentation**: When rendering, [Comment.php](file:///c:/laragon/www/musicsocial-main/app/Models/Comment.php#L64) strips the `[SONG:...]` tag so the comment text displays cleanly without raw URLs.
4. **Dynamic Card Hydration**: [comment.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/comment.blade.php#L8-L27) uses Alpine.js to detect the embedded song ID, asynchronously fetches the details via `/search/tracks/{id}`, and dynamically renders a beautiful glassmorphism play card beneath the comment text.

---

### Phase 3: Intelligence Processing (Netcentric Service)
Laravel requests recommendations from the Flask microservice. The microservice dynamically decides which mathematical model to run.

*   **Laravel integration**: [RecommendationService.php](file:///c:/laragon/www/musicsocial-main/app/Services/RecommendationService.php)

#### A. Decision Engine Thresholds
The algorithm computes `effective_interactions = max(interactions, shelf_count)`. Due to the mandatory onboarding flow, **the COLD start phase is bypassed entirely for all real users**:
*   **HOT Phase (Collaborative SVD)**: Triggered when `effective_interactions >= 10` (users with deep activity history).
*   **WARM Phase (TF-IDF Cosine Similarity)**: Triggered when `5 <= effective_interactions < 10`. **This is the starting phase for all new users** because the onboarding flow forces them to select exactly 5 shelf songs, making their initial `effective_interactions = 5`.
*   **COLD Phase (Popularity Fallback)**: A backend safety net in the Python service that only triggers if `effective_interactions < 5` (e.g. data corruption or test accounts that bypassed onboarding). Under normal operation, this is unreachable.

#### B. The Recommendation Equations
The final score fuses algorithmic preference with social authority:
$$\text{Total Score} = (\text{Base Score} \times 0.7) + (\text{Social Trust Boost} \times 0.3)$$

1. **Active SVD Predictor** ([app.py:L1340](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1340)):
   Runs collaborative matrix factorization to predict ratings, adding a $+0.4$ boost for explicit favorite artist matches.
   $$\text{Base Score} = \hat{r}_{u,i} + \text{Context Boost (0.4)}$$
2. **Warm Content Predictor** ([app.py:L1383](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1383)):
   Computes the average TF-IDF vector of a user's liked and shelf songs, then matches candidates using Cosine Similarity:
   $$\text{Cosine Similarity} = \frac{\vec{U} \cdot \vec{S}}{\|\vec{U}\| \|\vec{S}\|}$$
3. **Social Trust Boost** ([app.py:L873](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L873)):
   Calculates recommendations weight based on social influence and dilutes it if the user follows a massive amount of accounts:
   $$\text{Trust} = \frac{\ln(1 + \text{Followers}_{sharer}^{0.7})}{1 + 0.5 \times \ln(1 + \text{Following}_{active})}$$
   $$\text{Social Boost} = \text{Trust} \times R_m$$
   Where relationship multiplier $R_m$ is:
   *   `1.0` for collaborative playlist peers.
   *   `0.8` for followed users (friends).
   *   `0.3` for general community/strangers.

#### Collaborative Filtering SVD & Context Boost Code: [recommender_service/app.py](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1340-L1359)
```python
# Predict rating using SVD model
for song_id in candidates:
    pred = algo.predict(user_id, song_id)
    current_score = pred.est
    
    # Apply context boost (+0.4) for favorite artists
    artist_matched = False
    if song_id in songs_metadata:
        artist = songs_metadata[song_id]['artist_name']
        if artist and artist.lower().strip() in liked_artists:
            current_score += 0.4
            artist_matched = True
```

##### How It Works (SVD & Context Boosting):
1. **Algorithmic Prediction**: Uses the pre-trained SVD collaborative filtering model to predict how much the user will enjoy a candidate song on a 1-5 scale.
2. **Artist Matching**: Cross-references the candidate song's artist name with the list of artists the user has previously liked or shared.
3. **Contextual Boost**: Adds a $+0.4$ boost directly to the SVD prediction score to ensure that songs by the user's favorite artists bubble up to the top.

#### TF-IDF Warm-Start Vectorization Code: [recommender_service/app.py](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1056-L1076)
```python
# Transform user liked songs using pre-built cache vectorizer
user_features_matrix = tfidf.transform(user_liked_songs_df['features'])

# Calculate the user's tastes vector profile average
user_profile = np.asarray(user_features_matrix.mean(axis=0))

# Compute cosine similarity array vs. all other songs
similarities = cosine_similarity(user_profile, all_features_matrix)[0]
```

##### How It Works (TF-IDF Similarity):
1. **Transform User Taste**: Converts the metadata features (genres/artists) of the user's shelf and liked songs into a sparse vector space using the cached TF-IDF vectorizer.
2. **Music Taste Profile**: Computes the mean vector to construct a single profile representing the user's overall music taste dimensions.
3. **Cosine Similarity**: Measures the mathematical angle between the user taste profile vector and all other candidate song vectors to score metadata overlaps.

#### Trust Score Calculation Code: [recommender_service/app.py](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L903-L915)
```python
# Dampened follower influence
numerator = math.log(1.0 + (pow(sharer_friends, 0.7)))

# Halved dilution based on selectivity
denominator = 1.0 + (0.5 * math.log(1.0 + active_user_friends))

trust = numerator / denominator
```

##### How It Works (Social Trust Math):
1. **Superstar Dampening (Numerator)**: Applies a $0.7$ exponent to the sharer's follower count to flatten the curve, ensuring mainstream influencers' recommendations don't completely drown out regular friends' shares.
2. **Dilution Adjustment (Denominator)**: Takes the active user's following count and halves the penalty using the $0.5$ factor to handle selective vs. social users fairly.
3. **Trust Score**: Divides the dampened influence by dilution to compute the peer-to-peer trust score.

---

### Phase 4: Data Egress (Hydration & UI Display)
The Laravel controller fetches recommendations, applies contextual interaction filters, retrieves full song metadata, and feeds the view.

*   **Primary Page View**: [discovery.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php)
*   **UI Components**:
    *   [discovery-card.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/discovery-card.blade.php)
    *   [who-to-follow.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/who-to-follow.blade.php)
    *   [sidebar-right.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/sidebar-right.blade.php)

#### Egress Filter & Sorting Code: [DiscoveryController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/DiscoveryController.php#L50-L99)
```php
$rawRecommendations = $this->recommendationService->getRecommendations($user->id);

if (!empty($rawRecommendations)) {
    $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
    
    // Fetch and remove existing listener interactions to prevent repeating suggestions
    $interactedSongIds = \App\Models\SongInteraction::where('user_id', $user->id)
                            ->pluck('song_id')->toArray();
    $filteredSongIds = array_diff($recommendedSongIds, $interactedSongIds);
    
    // Select top 12 recommendation items
    $top12Ids = array_slice($filteredSongIds, 0, 12);
    $recommendationData = collect($rawRecommendations)->keyBy('song_id');
    $recommendedSongs = Song::whereIn('id', $top12Ids)->get();

    // Re-apply score sorting
    $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
        return $recommendationData[$song->id]['score'] ?? 0;
    })->values();

    $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
        $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
        $song->score = $recommendationData[$song->id]['score'] ?? null;
        return $song;
    });
}
```

##### How It Works (Laravel Hydration & Post-Filtering):
1. **Raw Retrieval**: Laravel fetches recommendation lists directly from the microservice.
2. **Historical Filtering**: Performs a `SongInteraction` query to exclude songs the active user has already heard, liked, or disliked.
3. **Trim & Hydration**: Trims results to the top 12, queries full models using `whereIn`, re-sorts them by Python score order, and attaches the recommendation reason strings to the collection before displaying the Blade views.

---

## 3. Real-Time & Persistent Playback Subsystem

The application provides a seamless music experience where playback persists across page navigation, resolving the player resetting issue.

*   **HTML Layout**: [app.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/layouts/app.blade.php) loads the Spotify Web Playback SDK scripts inside the `<head>`.
*   **Navigation Components**: [navigation.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/layouts/navigation.blade.php) and [mobile-bottom-nav.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/mobile-bottom-nav.blade.php).
*   **Playback Proxy Routing**: [SpotifyPlayerController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyPlayerController.php)

#### Player Alpine Component Init Listener: [spotify-web-player.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/components/spotify-web-player.blade.php#L144-L176)
```javascript
init() {
    console.log('[SpotifyPlayer] Alpine mounted — isPremium:', this.isPremium);

    // Attach native audio preview listeners for free accounts
    if (!window.__spotifyAudioListenersAttached) {
        window.__spotifyAudioListenersAttached = true;
        window.__spotifyNativeAudio.addEventListener('timeupdate', () => {
            if (!this.isPremium) this.positionMs = window.__spotifyNativeAudio.currentTime * 1000;
        });
        window.__spotifyNativeAudio.addEventListener('ended', () => {
            if (!this.isPremium) { this.isPaused = true; this.positionMs = 0; }
        });
    }

    if (this.isPremium) {
        this.initializePlayer();
    }

    // Intercept global triggers to play a song and wake up the player UI
    window.toggleSpotifyPlayer = (spotifyUri, meta) => {
        this.playerVisible = true;
        this.collapsed     = false;
        this.noPreview     = false;
        this.isLoading     = true;

        if (meta) {
            this.trackName  = meta.name   || null;
            this.artistName = meta.artist || null;
            this.albumArt   = meta.art    || null;
        }

        this._doPlay(spotifyUri, meta);
    };
}
```

##### How It Works (Persistent Web Player Bridge):
1. **Free Account Listener**: If the logged-in user doesn't have Spotify Premium, it registers event listeners directly onto HTML5 preview audios to track position and completion.
2. **Premium Device Initializer**: If they are Premium, it dynamically inserts Spotify SDK script and connects to the virtual playback target device.
3. **Global Play Bridge**: Mounts `window.toggleSpotifyPlayer` as a global handle. When users click play on any dashboard share card or discovery list, the event calls this bridge to wake up the persistent player UI and streams the track.

---

## 4. Netcentric Integration Points

Three vital network dependencies connect the Laravel core:
1.  **Spotify Web API (REST - OAuth client flow)**: Handled by [SpotifyService.php](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php). Manages token updates, search routing, and metadata retrieval. Supporting routing controllers are [SpotifySearchController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifySearchController.php) and [SpotifyImportController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php).
2.  **External Information Services (REST)**: [MusicBrainzService.php](file:///c:/laragon/www/musicsocial-main/app/Services/MusicBrainzService.php) and [DiscogsService.php](file:///c:/laragon/www/musicsocial-main/app/Services/DiscogsService.php) fetch genre tags to create content profiling indexes.
3.  **Flask Recommendation Service (HTTP/JSON)**: Laravel calls this server synchronously with a 5s connection timeout and 3 retries, guaranteeing reliable discovery page loads.

---

## 5. Page Routing & Rendering Map

This map outlines how URLs resolve to backend controller actions and where their corresponding Blade view template layouts reside in the project directory.

| Feature Page / Scope | URL / Route Definition | Controller Action | Compiled Blade View Path |
| :--- | :--- | :--- | :--- |
| **Home Feed (Dashboard)** | `/dashboard` ([web.php:84](file:///c:/laragon/www/musicsocial-main/routes/web.php#L84)) | `FeedController@index` ([FeedController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php)) | [dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/dashboard.blade.php) |
| **Discovery Page** | `/discovery` ([web.php:186](file:///c:/laragon/www/musicsocial-main/routes/web.php#L186)) | `DiscoveryController@index` ([DiscoveryController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/DiscoveryController.php)) | [discovery.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/discovery.blade.php) |
| **User Profiles** | `/users/{user:name}` ([web.php:143](file:///c:/laragon/www/musicsocial-main/routes/web.php#L143)) | `UserProfileController@show` ([UserProfileController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php)) | [profile/show.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/profile/show.blade.php) |
| **Playlist Management** | `/playlists` ([web.php:103](file:///c:/laragon/www/musicsocial-main/routes/web.php#L103)) | `PlaylistController` ([PlaylistController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php)) | [playlists/index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/index.blade.php) & [playlists/show.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/playlists/show.blade.php) |
| **Shelf Onboarding** | `/onboarding/genres` ([web.php:33](file:///c:/laragon/www/musicsocial-main/routes/web.php#L33)) | `OnboardingController@genres` ([OnboardingController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php)) | [onboarding/genres.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/onboarding/genres.blade.php) |
| **Account Settings** | `/settings` ([web.php:168](file:///c:/laragon/www/musicsocial-main/routes/web.php#L168)) | `SettingsController@index` ([SettingsController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SettingsController.php)) | [settings/index.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/settings/index.blade.php) |
| **Admin Dashboard** | `/admin` ([web.php:41](file:///c:/laragon/www/musicsocial-main/routes/web.php#L41)) | `AdminController@dashboard` ([AdminController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php)) | [admin/dashboard.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/dashboard.blade.php) |
| **Admin Retrain Algo** | `/admin/retrain` ([web.php:49](file:///c:/laragon/www/musicsocial-main/routes/web.php#L49)) | `AdminController@retrainPage` ([AdminController.php](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/AdminController.php)) | [admin/retrain.blade.php](file:///c:/laragon/www/musicsocial-main/resources/views/admin/retrain.blade.php) |
