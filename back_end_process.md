# Reso Backend Processes: Codebook & Descriptions

This document serves as the technical codebook for the Reso platform backend. It contains the **full codebase sections** along with brief descriptions and formal figure captions for each of the core backend workflows.

---

## Table of Contents
1. [4.2.1 Authentication & Third-Party OAuth Identity & Scopes](#421-authentication--third-party-oauth-identity--scopes)
2. [4.2.2 Onboarding Initial Profile Construction & Genre Sanitization](#422-onboarding-initial-profile-construction--genre-sanitization)
3. [4.2.3 Feature Vectorization (TF-IDF Weighting)](#423-feature-vectorization-tf-idf-weighting)
4. [4.2.4 Cosine Similarity Matching (Warm Start)](#424-cosine-similarity-matching-warm-start)
5. [4.2.5 Interaction Weighting & SVD Model Training](#425-interaction-weighting--svd-model-training)
6. [4.2.6 Social Trust Boosting](#426-social-trust-boosting)
7. [4.2.7 Dynamic Preference Boosting & Identity Aggregation](#427-dynamic-preference-boosting--identity-aggregation)
8. [4.2.8 Recommendation Tiered Fallback Safeties](#428-recommendation-tiered-fallback-safeties)
9. [4.2.9 Vector Identity Centroid Transformation](#429-vector-identity-centroid-transformation)
10. [4.2.10 Feed Generation & Injection](#4210-feed-generation--injection)
11. [4.2.11 Collaborative Authorization Enforcement](#4211-collaborative-authorization-enforcement)
12. [4.2.12 Threaded Comment Resolution Logic](#4212-threaded-comment-resolution-logic)
13. [4.2.13 In-Text Upvoting, Mention Notifications, & Recursive Reply Cleanup](#4213-in-text-upvoting-mention-notifications--recursive-reply-cleanup)
14. [4.2.14 Behavioral Auditing](#4214-behavioral-auditing)
15. [4.2.15 External Library Export (Spotify Synchronization)](#4215-external-library-export-spotify-synchronization)
16. [4.2.16 External Playlist Import & Ingestion Mechanics](#4216-external-playlist-import--ingestion-mechanics)
17. [4.2.17 Persistent Spotify Web Player, Audio Previews & Global Iframe Previews](#4217-persistent-spotify-web-player-audio-previews--global-iframe-previews)

---

## 4.2.1 Authentication & Third-Party OAuth Identity & Scopes

### Spotify Authorization Redirect & Scopes
**Description:** Requests authorization from Spotify using Socialite, applying low-privilege scopes for reading profile info, checking player status, scraping streaming telemetry (`user-read-recently-played`), and writing playlists (`playlist-modify`).
* **Source:** [SocialAuthController.php (L16-L39)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L16-L39)

```php
    public function redirect($provider)
    {
        // For Spotify, we need specific scopes to read user data and recently played (future proofing)
        if ($provider === 'spotify') {
            return Socialite::driver('spotify')
                ->stateless()
                ->scopes([
                    'user-read-email', 
                    'user-read-private', 
                    'user-read-recently-played',
                    'playlist-read-private',
                    'playlist-read-collaborative',
                    'playlist-modify-public',
                    'playlist-modify-private',
                    'streaming',
                    'user-read-playback-state',
                    'user-modify-playback-state'
                ])
                ->redirect();
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }
```
*Figure 4.1: Spotify Authorization Redirect & Scopes Configuration*

### Spotify On-Demand Token Refresh
**Description:** Refreshes the user's Spotify access token on-demand if a request to the Spotify API returns a `401 Unauthorized` response.
* **Source:** [SpotifyService.php (L469-L502)](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L469-L502)

```php
    public function refreshUserToken($user)
    {
        try {
            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->spotify_refresh_token,
                'client_id' => config('services.spotify.client_id'),
                'client_secret' => config('services.spotify.client_secret'),
            ]);

            if ($response->successful()) {
                $check = $response->json();
                $accessToken = $check['access_token'] ?? null;
                
                if (!$accessToken) {
                    Log::error('Spotify Token Refresh: Access token missing in response', $check);
                    return null;
                }

                // Update user
                $user->spotify_token = $accessToken;
                if (isset($check['refresh_token'])) {
                    $user->spotify_refresh_token = $check['refresh_token'];
                }
                $user->save();
                return $accessToken;
            }
            
            Log::error('Spotify Token Refresh Failed: ' . $response->status() . ' - ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Spotify Token Refresh Exception: ' . $e->getMessage());
        }
        return null;
    }
```
*Figure 4.2: Spotify On-Demand Token Refresh Routine*

### Google OAuth Identity Mapping & Deduplication Callback
**Description:** Step-by-step resolution script triggered on Google authentication callback. It prevents account duplication by first querying by the Google unique key, then looking up by email (merging accounts and marking them verified if found), and provisioning a new user if no match exists.
* **Source:** [SocialAuthController.php (L153-L212)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L153-L212)

```php
    protected function findOrCreateUser($socialUser, $provider)
    {
        // 1. Check if user exists by Provider ID
        $user = User::where($provider . '_id', $socialUser->getId())->first();

        if ($user) {
            // Update tokens if likely changed (especially Spotify)
            if ($provider === 'spotify') {
                $user->update([
                    'spotify_token' => $socialUser->token,
                    'spotify_refresh_token' => $socialUser->refreshToken,
                    'spotify_product' => $socialUser->user['product'] ?? null,
                ]);
            }
            return $user;
        }

        // 2. Check if user exists by Email (link accounts)
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link the provider to existing user
            $user->update([
                $provider . '_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(), // Optional: Update avatar
                'email_verified_at' => $user->email_verified_at ?? now(), // Mark as verified if not already
            ]);
            
            if ($provider === 'spotify') {
                $user->update([
                    'spotify_token' => $socialUser->token,
                    'spotify_refresh_token' => $socialUser->refreshToken,
                    'spotify_product' => $socialUser->user['product'] ?? null,
                ]);
            }

            return $user;
        }

        // 3. Create new user
        $newUser = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => null, // Allow users to set this later
            'email_verified_at' => now(), // Assume verified by provider
            $provider . '_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
        ]);

        if ($provider === 'spotify') {
            $newUser->update([
                'spotify_token' => $socialUser->token,
                'spotify_refresh_token' => $socialUser->refreshToken,
                'spotify_product' => $socialUser->user['product'] ?? null,
            ]);
        }

        return $newUser;
    }
```
*Figure 4.3: Google OAuth Identity Mapping & Account Merging Callback*

---

## 4.2.2 Onboarding Initial Profile Construction & Genre Sanitization

### Initial Profile Curation
**Description:** Validates and stores the user's initial shelf configuration (between 5 and 10 tracks, skip disabled) and adds a positive interaction in `song_interactions` (so onboarding picks are filtered out from dashboard recommendation feeds).
* **Source:** [OnboardingController.php (L48-L89)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L48-L89)

```php
    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_ids' => 'required|array|min:5|max:10',
            'song_ids.*' => 'string'
        ]);

        $user = $request->user();

        // Clear existing shelf
        $user->shelfSongs()->delete();

        foreach ($validated['song_ids'] as $index => $spotifyId) {
            // Fetch/Create the song in local DB
            $trackData = $this->spotifyService->getTrack($spotifyId);
            
            if (isset($trackData['error']) || !isset($trackData['song'])) {
                continue; // Skip if invalid
            }
            
            $song = $trackData['song'];

            // Save to the Shelf
            UserShelfSong::create([
                'user_id' => $user->id,
                'song_id' => $spotifyId, // Spotify ID (String)
                'position' => $index,
            ]);

            // Record as a song interaction for Discovery page exclusion filtering.
            SongInteraction::updateOrCreate(
                ['user_id' => $user->id, 'song_id' => $song->id],
                ['type' => 'like', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $user->update(['is_onboarded' => true]);

        return response()->json(['message' => 'Shelf curated successfully.', 'redirect' => route('dashboard', ['feed' => 'explore'])]);
    }
```
*Figure 4.4: Initial Profile Construction & Shelf Curation*

### Multi-Source Genre Ingestion Falling Pipeline
**Description:** Resolves metadata using a sequential fallback mechanism. Caches completed arrays for 7 days.
* **Source:** [SpotifyService.php (L210-L417)](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L210-L417)

```php
    public function getGenresWithSources(string $trackId): array
    {
        return Cache::remember("genres_track_v2_{$trackId}", 60 * 60 * 24 * 7, function () use ($trackId) {
            // ... (Fetches track and artist info from Spotify API) ...
            
            // 1. Spotify Artist Genres
            $allGenres = array_merge($allGenres, $spotifyArtistGenres);

            // 2. Spotify Album Genres
            $allGenres = array_merge($allGenres, $spotifyAlbumGenres);

            // 3. Discogs Genres Fallback
            $discogsTags = $discogsService->getGenres($artistName, $trackName);
            $allGenres = array_merge($allGenres, $discogsTags);

            // 4. MusicBrainz Fallback (If unique count < 3)
            $mbGenres = $musicBrainzService->getArtistGenres($artistName);
            $allGenres = array_merge($allGenres, $mbCleaned);
            
            // 5. iTunes (Apple) Search API Fallback (If unique count < 3)
            $allGenres = array_merge($allGenres, $extractedTag);
            
            // 6. YouTube video tags Fallback (If unique count === 0)
            $allGenres = array_merge($allGenres, $ytTags);
            
            // 7. Contextual Playlist Search Fallback (If unique count === 0)
            $allGenres = array_merge($allGenres, $playlistGenres);

            // Final Clean and Sanitize
            $uniqueGenres = $cleaner->clean($allGenres);
            $finalGenres = array_slice($uniqueGenres, 0, 5);

            // 8. DB Sibling track inheritance (If final count is empty)
            if (empty($finalGenres)) {
                $sibling = \App\Models\Song::where('artist_name', $artistName)
                    ->whereNotNull('genres')
                    ->where('genres', '!=', '[]')
                    ->first();
                if ($sibling) {
                    $finalGenres = json_decode($sibling->genres, true);
                }
            }

            return ['genres' => $finalGenres, 'sources' => $debugSources];
        });
    }
```
*Figure 4.5: Multi-Source Genre Ingestion Falling Pipeline*

### Genre Sanitization Pipeline
**Description:** Normalizes genres by stripping blocklisted keywords, checking user overrides (aliases), verifying entries against Beets whitelists (loaded from `genres.txt`), removing numbers, and deduplicating.
* **Source:** [GenreCleanerService.php (L140-L191)](file:///c:/laragon/www/musicsocial-main/app/Services/GenreCleanerService.php#L140-L191)

```php
    public function clean(array $genres, bool $strict = false): array
    {
        $cleaned = [];

        foreach ($genres as $genre) {
            // 1. Lowercase & Trim
            $g = strtolower(trim($genre));

            // 2. Check Blocklist (Skip garbage)
            if (in_array($g, $this->blocklist)) {
                continue;
            }

            // 3. Apply Alias Mapping (Fix typos)
            if (isset($this->aliases[$g])) {
                $g = $this->aliases[$g];
            }

            // 4. Beets Whitelist Normalization
            $inWhitelist = isset($this->beetsWhitelist[$g]);
            if ($inWhitelist) {
               $g = $this->beetsWhitelist[$g];
            } elseif ($strict) {
                // Drop if strict mode and not in whitelist
                continue;
            }

            // Remove tags that are too short to be real genres
            if (strlen($g) < 2) {
                continue;
            }

            // Regex to filter out dates (YYYY-MM-DD) or just Years (YYYY)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $g) || preg_match('/^\d{4}$/', $g)) {
                continue;
            }

            $cleaned[] = $g;
        }

        // 5. Remove Duplicates
        $unique = array_unique($cleaned);

        // 6. Re-index array (0, 1, 2...)
        return array_values($unique);
    }
```
*Figure 4.6: Genre Sanitization & Whitelist Filtering Pipeline*

---

## 4.2.3 Feature Vectorization (TF-IDF Weighting)

### Song Feature String Construction
**Description:** Pre-processes track artists and genres, repeating the artist name twice to double its mathematical significance in vector space calculations.
* **Source:** [app.py (L975-L1012)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L975-L1012)

```python
def build_song_features(song_row):
    """
    Build a text feature string from song metadata for TF-IDF vectorization.
    """
    features = []
    
    # Add artist name (repeated twice for higher weight)
    if pd.notna(song_row['artist_name']):
        artist = song_row['artist_name'].lower().replace(' ', '_')
        features.extend([artist] * 2)  # Repeat for emphasis
    
    # Add genres
    if pd.notna(song_row['genres']):
        try:
            genres = json.loads(song_row['genres'])
            genre_tokens = [g.lower().replace(' ', '_') for g in genres]
            features.extend(genre_tokens)
        except:
            pass  # Skip if genres can't be parsed
    
    return ' '.join(features) if features else ''
```
*Figure 4.7: Song Feature String Construction for TF-IDF Vectorization*

---

## 4.2.4 Cosine Similarity Matching (Warm Start)

### Cosine TF-IDF Vector Taste Similarity Engine
**Description:** Calculates the cosine distance between the user taste centroid (average coordinate vector of historical selections) and all catalog songs.
* **Source:** [app.py (L1013-L1115)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1013-L1115)

```python
def content_based_similarity_tfidf(user_id, all_songs_df, user_liked_songs_df):
    """
    Calculate content-based similarity using TF-IDF and cosine similarity (OPTIMIZED).
    """
    if user_liked_songs_df.empty or all_songs_df.empty:
        return []
    
    try:
        # OPTIMIZATION: Get or build cached TF-IDF matrix (avoids recomputation)
        result = get_or_build_tfidf_cache(all_songs_df)
        if result[0] is None:
            return []
        
        tfidf, all_features_matrix, cached_songs_df = result
        
        # Build features for user's liked songs only (not all songs)
        user_liked_songs_df = user_liked_songs_df.copy()
        user_liked_songs_df['features'] = user_liked_songs_df.apply(build_song_features, axis=1)
        user_liked_songs_df = user_liked_songs_df[user_liked_songs_df['features'] != '']
        
        if user_liked_songs_df.empty:
            return []
        
        # Transform user songs using cached vectorizer (no fitting needed)
        user_features_matrix = tfidf.transform(user_liked_songs_df['features'])
        
        # Calculate average user profile vector (taste centroid)
        user_profile = np.asarray(user_features_matrix.mean(axis=0))
        
        # Calculate cosine similarity between user profile and all songs (cached matrix)
        similarities = cosine_similarity(user_profile, all_features_matrix)[0]
        
        # Build predictions from similarity scores
        predictions = []
        for idx, similarity_score in enumerate(similarities):
            if similarity_score > 0.1:
                song_id = int(cached_songs_df.iloc[idx]['id'])
                song_data = cached_songs_df.iloc[idx]
                
                # Build detailed reason based on what matched
                reason_parts = []
                artist_matched = False
                if pd.notna(song_data.get('artist_name')):
                    artist = song_data['artist_name']
                    user_artists = {row['artist_name'] for _, row in user_liked_songs_df.iterrows() if pd.notna(row.get('artist_name'))}
                    if artist in user_artists:
                        reason_parts.append(f"Deep cut from {artist}")
                        artist_matched = True
                    else:
                        reason_parts.append(f"Matches your sound profile")
                
                reason = " · ".join(reason_parts) if reason_parts else 'Fits your music style'
                
                predictions.append({
                    'song_id': song_id,
                    'score': float(similarity_score),
                    'reason': reason,
                    'artist_matched': artist_matched
                })
        
        return predictions
    
    except Exception as e:
        print(f"Error in content_based_similarity_tfidf: {e}")
        return []
```
*Figure 4.8: Cosine TF-IDF Vector Taste Similarity Engine*

### Jaccard Similarity Fallback
**Description:** Runs a direct string Jaccard intersection calculation if TF-IDF matrix operations fail or return empty collections.
* **Source:** [app.py (L1121-L1165)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1121-L1165)

```python
def content_based_similarity(user_id, all_songs_df, liked_genres, liked_artists):
    """
    Calculates similarity based on Genre and Artist overlap for Cold Start users.
    """
    predictions = []
    
    for _, song in all_songs_df.iterrows():
        score = 0.0
        reasons = []
        
        # Artist Match
        artist_matched = False
        if song['artist_name'] in liked_artists:
            score += 0.5
            reasons.append(f"Same artist: {song['artist_name']}")
            artist_matched = True
            
        # Genre Overlap (Jaccard Index-ish)
        song_genres = set()
        if song['genres']:
            try:
                g_list = json.loads(song['genres'])
                song_genres = {g.lower() for g in g_list}
            except:
                pass
        
        if song_genres and liked_genres:
            intersection = song_genres.intersection(liked_genres)
            if intersection:
                overlap_score = len(intersection) / len(song_genres.union(liked_genres)) # Jaccard
                score += overlap_score * 0.5 # Weight for genre
                reasons.append(f"Similar genres: {', '.join(list(intersection)[:2])}")
        
        if score > 0:
             predictions.append({
                'song_id': int(song['id']),
                'score': float(score),
                'reason': " & ".join(reasons),
                'artist_matched': artist_matched
            })
            
    return predictions
```
*Figure 4.9: Jaccard Similarity Fallback Calculation*

---

## 4.2.5 Interaction Weighting & SVD Model Training

### Interaction Signal Weighting & Log-Flattening
**Description:** Fetches user actions, aggregates positive scores, applies log-flattening, and returns user interaction matrices.
* **Source:** [app.py (L202-L246)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L202-L246)

```python
            # Combine all positive engagements (Likes + Shares + Comments + Playlists + Shelf + Discovery Likes)
            engagement_df = pd.concat([likes_df, shares_df, comments_df, playlist_df, shelf_df, positive_direct], ignore_index=True)
            
            if not engagement_df.empty:
                # Sum scores per user-song pair (rui = Total Engagement Score)
                grouped_df = engagement_df.groupby(['user_id', 'song_id'])['score'].sum().reset_index()
                
                # Apply Logarithmic Formula: cui = 1 + ln(1 + rui)
                grouped_df['interaction'] = 1 + np.log(1 + grouped_df['score'].astype(float))
            else:
                grouped_df = pd.DataFrame(columns=['user_id', 'song_id', 'interaction'])

            # Dislikes (Strong Negative -1.0)
            # Combine traditional share-based dislikes with direct Discovery passes
            dislikes_df = pd.concat([dislikes_df, negative_direct], ignore_index=True)

            # Combine Positives + Dislikes (keeps positive on duplicates)
            interactions_df = pd.concat([grouped_df.rename(columns={'song_id': 'item_id'}), 
                                         dislikes_df.rename(columns={'song_id': 'item_id'})], ignore_index=True)
            interactions_df = interactions_df.drop_duplicates(subset=['user_id', 'item_id'], keep='first')
```
*Figure 4.10: Interaction Signal Weighting & Logarithmic Scaling*

### Collaborative Filtering SVD Model Training
**Description:** Fits SVD model over 20 iterations using stochastic gradient descent and saves the trained artifact to `surprise_model.pkl`.
* **Source:** [app.py (L253-L279)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L253-L279)

```python
def train_and_save_model():
    global algo
    if os.path.exists(MODEL_PATH):
        os.remove(MODEL_PATH)
        print(f"Deleted existing model file: {MODEL_PATH}")
    print("Fetching data for model training...")
    interactions_df = fetch_data_from_db()

    if interactions_df.empty:
        print("No data to train the model. Skipping training.")
        return

    reader = Reader(rating_scale=(-1, 6)) 
    data = Dataset.load_from_df(interactions_df[['user_id', 'item_id', 'interaction']], reader)

    trainset = data.build_full_trainset()

    print("Training SVD model...")
    algo = SVD(n_epochs=20, lr_all=0.005, reg_all=0.02, random_state=42)
    algo.fit(trainset)
    print("Model training complete.")

    joblib.dump(algo, MODEL_PATH)
```
*Figure 4.11: Collaborative Filtering SVD Model Training*

---

## 4.2.6 Social Trust Boosting

### Power Log trust Score Calculation
**Description:** Evaluates trust between accounts based on authority/influence and dilution.
* **Source:** [app.py (L873-L915)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L873-L915)

```python
def calculate_trust(active_user_friends, sharer_friends):
    """
    Calculate trust score using logarithmic formula based on social network theory.
    """
    active_user_friends = max(1, active_user_friends)
    sharer_friends = max(1, sharer_friends)
    
    # 1. Numerator (Influence): ln(1 + |F_sharer|^0.7)
    numerator = math.log(1.0 + (pow(sharer_friends, 0.7)))
    
    # 2. Denominator (Dilution): 1 + 0.5 * ln(1 + |F_active|)
    denominator = 1.0 + (0.5 * math.log(1.0 + active_user_friends))
    
    trust = numerator / denominator
    
    return trust
```
*Figure 4.12: Power Log Social Trust Score Calculation*

---

## 4.2.7 Dynamic Preference Boosting & Identity Aggregation

### Liked Artists Identity Extraction
**Description:** Aggregates a set of unique lowercased artists linked to a user via likes, posts, shelf selections, and playlist curations.
* **Source:** [app.py (L1235-L1260)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php)

```python
def get_liked_artists(user_id, connection):
    liked_artists_query = text("""
        SELECT DISTINCT so.artist_name
        FROM likes l
        JOIN shares s ON l.share_id = s.id
        JOIN songs so ON s.song_id = so.id
        WHERE l.user_id = :user_id
        UNION
        SELECT DISTINCT so.artist_name
        FROM shares s
        JOIN songs so ON s.song_id = so.id
        WHERE s.user_id = :user_id
        UNION
        SELECT DISTINCT so.artist_name
        FROM user_shelf_songs uss
        JOIN songs so ON uss.song_id = so.spotify_track_id
        WHERE uss.user_id = :user_id
        UNION
        SELECT DISTINCT so.artist_name
        FROM playlist_songs ps
        JOIN songs so ON ps.song_id = so.spotify_track_id
        WHERE ps.added_by_user_id = :user_id
    """)
    liked_artists_df = pd.read_sql(liked_artists_query, connection, params={'user_id': user_id})
    return {name.lower().strip() for name in liked_artists_df['artist_name'].unique() if name}
```
*Figure 4.13: Liked Artists Identity Extraction*

---

## 4.2.8 Recommendation Tiered Fallback Safeties

### Pipeline Fallback Progression
**Description:** Fills candidate slots up to 12 if active filters leave recommendation logs sparse, querying genre popularity (Tier 1), community popularity (Tier 2), or random selection (Tier 3).
* **Source:** [app.py (L1476-L1563)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1476-L1563)

```python
            if len(cf_predictions) < 12:
                    print(f"Low result count ({len(cf_predictions)}). Filling with Tiered Fallback.")
                    needed = 12 - len(cf_predictions)
                    existing_ids = {p['song_id'] for p in cf_predictions}
                    
                    # --- TIER 1: Genre-Aware Fallback ---
                    if liked_genres:
                        valid_genres = [g for g in liked_genres if len(g) > 2]
                        if valid_genres:
                            genre_clauses = " OR ".join([f"genres LIKE '%\"{g}\"%' OR genres LIKE '%{g}%'" for g in valid_genres])
                            genre_pop_query = text(f"""
                                SELECT s.id as song_id, 
                                       (COUNT(l.id) + (SELECT COUNT(*) FROM shares sh WHERE sh.song_id = s.id) * 2) as popularity
                                FROM songs s
                                LEFT JOIN likes l ON l.share_id IN (SELECT id FROM shares WHERE song_id = s.id)
                                WHERE ({genre_clauses})
                                GROUP BY s.id
                                ORDER BY popularity DESC
                                LIMIT 20
                            """)
                            try:
                                genre_pop_df = pd.read_sql(genre_pop_query, connection)
                                for _, row in genre_pop_df.iterrows():
                                    s_id = int(row['song_id'])
                                    if s_id in existing_ids or s_id in user_interacted_song_ids:
                                        continue
                                    
                                    cf_predictions.append({
                                        'song_id': s_id,
                                        'score': 0.15,
                                        'reason': 'Vibe match: Based on your genre favorites'
                                    })
                                    existing_ids.add(s_id)
                                    if len(cf_predictions) >= 12:
                                        break
                            except Exception as e:
                                print(f"Error in genre-aware fallback: {e}")

                    # --- TIER 2: Global Popularity (Final Safety Net) ---
                    if len(cf_predictions) < 12:
                        needed = 12 - len(cf_predictions)
                        top_songs_query = text("""
                        SELECT s.id as song_id, 
                                (COUNT(l.id) + (SELECT COUNT(*) FROM shares sh WHERE sh.song_id = s.id) * 2) as popularity
                        FROM songs s
                        LEFT JOIN likes l ON l.share_id IN (SELECT id FROM shares WHERE song_id = s.id)
                        GROUP BY s.id
                        ORDER BY popularity DESC
                        LIMIT 30
                        """)
                        try:
                            top_songs_df = pd.read_sql(top_songs_query, connection)
                            filled_count = 0
                            for _, row in top_songs_df.iterrows():
                                s_id = int(row['song_id'])
                                if s_id in existing_ids or s_id in user_interacted_song_ids:
                                    continue

                                cf_predictions.append({
                                    'song_id': s_id,
                                    'score': 0.1, 
                                    'reason': 'Trending in the community'
                                })
                                existing_ids.add(s_id)
                                filled_count += 1
                                if filled_count >= needed:
                                    break
                        except Exception as e:
                            print(f"Error in global fallback: {e}")
                            # Final absolute fallback to random (Tier 3)
                            fallback_query = text("SELECT id FROM songs ORDER BY RAND() LIMIT 12")
                            fallback_df = pd.read_sql(fallback_query, connection)
                            for _, row in fallback_df.iterrows():
                                s_id = int(row['id'])
                                if s_id not in user_interacted_song_ids and s_id not in existing_ids:
                                    cf_predictions.append({
                                        'song_id': s_id,
                                        'score': 0.05,
                                        'reason': 'Discovered for you'
                                    })
```
*Figure 4.14: Recommendation Pipeline Tiered Fallback Safeties*

---

## 4.2.9 Vector Identity Centroid Transformation

### Coordinate Centroid Average Mapping
**Description:** Compresses historical feature matrices into a taste profile centroid.
* **Source:** [app.py (L1067-L1072)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1067-L1072)

```python
        # Transform user songs using cached vectorizer (no fitting needed)
        user_features_matrix = tfidf.transform(user_liked_songs_df['features'])
        
        # Calculate average user profile vector
        user_profile = np.asarray(user_features_matrix.mean(axis=0))
```
*Figure 4.15: Taste Profile Coordinate Centroid Average Mapping*

---

## 4.2.10 Feed Generation & Injection

### Dashboard Feed Composition
**Description:** Fetches recommendations and handles hydration and score-sorting.
* **Source:** [FeedController.php (L70-L110)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L70-L110)

```php
        // Fetch recommended shares
        $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedShares = collect();
        $recommendedSongs = collect();

        if (!empty($rawRecommendations)) {
            $recommendedShareIds = collect($rawRecommendations)->pluck('share_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('share_id');

            $recommendedShares = Share::where('is_deleted', false)
                                      ->whereIn('id', $recommendedShareIds)
                                      ->whereDoesntHave('dislikes', function ($query) use ($user) {
                                          $query->where('user_id', $user->id);
                                      })
                                      ->get();

            // Sort the recommended shares by score
            $recommendedShares = $recommendedShares->sortByDesc(function ($share) use ($recommendationData) {
                return $recommendationData[$share->id]['score'] ?? 0;
            });

            $recommendedShares = $recommendedShares->map(function ($share) use ($recommendationData) {
                $share->reason = $recommendationData[$share->id]['reason'] ?? 'Based on your taste';
                return $share;
            });

            $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('song_id');

            $recommendedSongs = Song::whereIn('id', $recommendedSongIds)->get();

            // Sort the recommended songs by score
            $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
                return $recommendationData[$song->id]['score'] ?? 0;
            });

            $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
                $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                return $song;
            });
        }
```
*Figure 4.16: Dashboard Feed Composition & Recommendation Hydration*

---

## 4.2.11 Collaborative Authorization Enforcement

### Collaborative Gateway Check
**Description:** Verifies user collaborative editing rights. Removing tracks is restricted to the playlist owner or the specific contributor who added that song.
* **Source:** [PlaylistController.php (L307-L333)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L307-L333)

```php
    public function removeSong(Playlist $playlist, $songId)
    {
        $user = Auth::user();
        
        // Find the specific entry in playlist_songs
        $playlistSong = PlaylistSong::where('playlist_id', $playlist->id)
                                    ->where('song_id', $songId)
                                    ->firstOrFail();

        // Check if user is the one who added the song OR is the owner of the playlist
        $isOwner = $playlist->collaborators()->where('user_id', $user->id)->where('role', 'owner')->exists();
        
        if ($playlistSong->added_by_user_id !== $user->id && !$isOwner) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'You are not authorized to remove this song.'], 403);
            }
            return back()->with('error', 'You are not authorized to remove this song.');
        }

        $playlistSong->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Song removed from playlist.');
    }
```
*Figure 4.17: Collaborative Gateway Editing Restrictions Check*

---

## 4.2.12 Threaded Comment Resolution Logic

### Eager Comment Loading
**Description:** Isolates root comments (no parent references) and eager-loads replies down multiple tree layers to prevent N+1 queries.
* **Source:** [ShareController.php (L235-L237)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php#L235-L237)

```php
        // Fetch top-level comments
        $commentsQuery = $share->comments()
            ->whereDoesntHave('parent')
            ->with(['user', 'replies.user', 'replies.replies.user']);
```
*Figure 4.18: Eager Comment Loading & Parent-Reply Resolution*

---

## 4.2.13 In-Text Upvoting, Mention Notifications, & Recursive Reply Cleanup

### Mentions regex Notification Dispatch
**Description:** Regex-based extraction of username mentions and dispatching notifications.
* **Source:** [CommentController.php (L92-L111)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L92-L111)

```php
        // 4. Handle Mention Notifications
        // Regex to find @mentions - matches @username
        preg_match_all('/@([\w\.\-]+)/', $validated['body'], $matches);
        
        $mentionedUserIds = [];
        if (!empty($matches[1])) {
            // Get unique usernames found in the comment
            $usernames = array_unique($matches[1]);
            
            // Find users with these names (except the commenter themselves)
            $usersToNotify = \App\Models\User::whereIn('name', $usernames)
                ->where('id', '!=', auth()->id())
                ->get();
                
            foreach ($usersToNotify as $user) {
                $user->notify(new \App\Notifications\UserMentionedNotification($comment));
            }

            $mentionedUserIds = $usersToNotify->pluck('id')->toArray();
        }
```
*Figure 4.19: Regex Username Mentions Extraction & Notification Dispatch*

### Recursive Cascade Comment Cleanup
**Description:** Soft-redacts comments if replies exist, and hard-deletes comments without replies, cleaning up orphaned threads recursively up the parent chain.
* **Source:** [CommentController.php (L192-L234)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L192-L234)

```php
        // 2. Check if the comment has replies
        if ($comment->replies()->exists()) {
             // Soft Delete: Keep the row, but redact content
             $comment->update(['body' => '[deleted]']);
             return response()->json(['message' => 'Comment deleted (thread preserved).']);
        }

        // 3. Simple Delete (No replies, so safe to remove)
        $parents = $comment->parent;
        
        $comment->delete();

        // 4. Recursive Cleanup for 'orphaned' soft-deleted parents
        foreach ($parents as $parent) {
            $this->cleanupParent($parent);
        }
```

```php
    /**
     * Recursively delete parents if they are soft-deleted and have no more children.
     */
    private function cleanupParent($comment)
    {
        // Reload to ensure we have fresh reply count
        $comment->loadCount('replies');

        if ($comment->body === '[deleted]' && $comment->replies_count === 0) {
            // Get grandparents before deleting this parent
            $grandParents = $comment->parent;
            
            $comment->delete(); // Hard delete this soft-deleted orphan

            // Check up the chain
            foreach ($grandParents as $grandParent) {
                $this->cleanupParent($grandParent);
            }
        }
    }
```
*Figure 4.20: Recursive Cascade Comment Thread Cleanup*

### In-Text Serialized Upvoting
**Description:** Toggles an upvote state stored serialized in the comment body string as `[UPVOTES:id1,id2,...]`.
* **Source:** [CommentController.php (L244-L258)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L244-L258)

```php
        if (preg_match('/\[UPVOTES:([^\]]*)\]/', $body, $matches)) {
            $ids = array_filter(explode(',', $matches[1]));
            if (in_array((string)$userId, $ids)) {
                // Remove
                $ids = array_diff($ids, [(string)$userId]);
            } else {
                // Add
                $ids[] = (string)$userId;
            }
            $newList = implode(',', $ids);
            $body = preg_replace('/\[UPVOTES:[^\]]*\]/', "[UPVOTES:{$newList}]", $body);
        } else {
            // Create new
            $body .= " [UPVOTES:{$userId}]";
        }
```
*Figure 4.21: In-Text Serialized Comment Upvoting*

---

## 4.2.14 Behavioral Auditing

### User Action Tracking & Cache Invalidation
**Description:** Captures real-time engagement logs (like, listen, dislike) and invalidates recommended feed caches.
* **Source:** [SongInteractionController.php (L14-L41)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SongInteractionController.php#L14-L41)

```php
    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'type' => 'required|in:listen,like,dislike',
        ]);

        $user = Auth::user();
        
        $interaction = SongInteraction::updateOrCreate(
            [
                'user_id' => $user->id,
                'song_id' => $validated['song_id'],
            ],
            [
                'type' => $validated['type']
            ]
        );

        // Clear the recommended songs cache for this user so they get fresh data next time
        \Illuminate\Support\Facades\Cache::forget("user_{$user->id}_recommended_songs");
```
*Figure 4.22: User Action Auditing & Recommendation Cache Invalidation*

---

## 4.2.15 External Library Export (Spotify Synchronization)

### Spotify Track URI Formatting & Batch Export
**Description:** Formats Spotify track IDs into URI structures and passes them to the batch dispatch services.
* **Source:** [PlaylistExportController.php (L58-L85)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistExportController.php#L58-L85)

```php
        $trackUris = [];
        foreach ($songs as $song) {
            if ($song->spotify_track_id) {
                $trackUris[] = 'spotify:track:' . $song->spotify_track_id;
            }
        }

        $trackUris = array_unique($trackUris); // Prevent duplicates

        if (empty($trackUris)) {
             return back()->with('error', 'No Spotify tracks found to export.');
        }

        // Create Playlist
        $playlist = $this->spotifyService->createPlaylist($user, $playlistName);

        if (!$playlist || !isset($playlist['id'])) {
            return back()->with('error', 'Failed to create Spotify playlist. Your session might have expired.');
        }

        // Add Tracks
        $success = $this->spotifyService->addTracksToPlaylist($user, $playlist['id'], $trackUris);
```
*Figure 4.23: Spotify Track URI Formatting & Batch Export*

---

## 4.2.16 External Playlist Import & Ingestion Mechanics

### Playlist Ingestion Transaction Loop
**Description:** Restricts payload size, processes database transactions, and uses `firstOrCreate` pivot keys.
* **Source:** [SpotifyImportController.php (L155-L180)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L155-L180)

```php
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($request->input('selected_tracks') as $trackJson) {
                $trackData = json_decode($trackJson, true);
                if (!$trackData || !isset($trackData['id'])) continue;

                // First fetch/create the song locally via basic metadata
                $song = Song::firstOrCreate([
                    'spotify_track_id' => $trackData['id'],
                ], [
                    'track_name' => $trackData['name'],
                    'artist_name' => $trackData['artist'],
                    'album_art_url' => $trackData['album_art'],
                    'spotify_url' => 'https://open.spotify.com/track/' . $trackData['id']
                ]);
                
                // Add to playlist_songs
                PlaylistSong::firstOrCreate([
                    'playlist_id' => $playlist->id,
                    'song_id' => $song->spotify_track_id,
                ], [
                    'added_by_user_id' => $user->id
                ]);
                $addedCount++;
            }
            \Illuminate\Support\Facades\DB::commit();
```
*Figure 4.24: Playlist Ingestion Transaction Loop*

---

## 4.2.17 Persistent Spotify Web Player, Audio Previews & Global Iframe Previews

### Spotify Player Token Controller
**Description:** Fetches and refreshes access tokens dynamically for Premium users' Web Playback SDK sessions, verifying token status on-the-fly and updating user details.
* **Source:** [SpotifyPlayerController.php (L14-L52)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyPlayerController.php#L14-L52)

```php
    public function token(SpotifyService $spotifyService)
    {
        $user = Auth::user();

        if (!$user || !$user->spotify_token) {
            return response()->json(['error' => 'Not authenticated with Spotify'], 401);
        }

        $response = \Illuminate\Support\Facades\Http::withToken($user->spotify_token)
            ->get('https://api.spotify.com/v1/me');

        if ($response->status() === 401 && $user->spotify_refresh_token) {
            $newToken = $spotifyService->refreshUserToken($user);
            if ($newToken) {
                // Fetch profile with new token to update product status
                $newResponse = \Illuminate\Support\Facades\Http::withToken($newToken)
                    ->get('https://api.spotify.com/v1/me');
                if ($newResponse->successful()) {
                    $user->update(['spotify_product' => $newResponse->json('product')]);
                }
                return response()->json(['token' => $newToken]);
            } else {
                return response()->json(['error' => 'Failed to refresh token'], 401);
            }
        }

        if ($response->successful()) {
            $product = $response->json('product');
            if ($user->spotify_product !== $product) {
                $user->update(['spotify_product' => $product]);
            }
        }

        return response()->json(['token' => $user->spotify_token]);
    }
```
*Figure 4.25: Spotify SDK Web Player Backend Token Controller*

### Global Iframe Previews Toggle
**Description:** Toggles the display of an interactive embedded Spotify Iframe Player on sidebars and playlist lists, resolving container visibility states, updating the frame URL, and shutting down other active players to prevent concurrent audio playbacks.
* **Source:** [app.blade.php (L63-L103)](file:///c:/laragon/www/musicsocial-main/resources/views/layouts/app.blade.php#L63-L103)

```javascript
            window.toggleSpotifyPreview = function(key, trackId) {
                if (!trackId) return;

                const container = document.getElementById('spe-container-' + key);
                const frame     = document.getElementById('spe-frame-' + key);
                const playIcon  = document.getElementById('spe-icon-play-' + key);
                const stopIcon  = document.getElementById('spe-icon-stop-' + key);

                if (!container || !frame) return;

                const embedUrl = 'https://open.spotify.com/embed/track/' + trackId + '?utm_source=generator&theme=0';
                const isOpen   = container.style.display !== 'none';

                // Close any other open preview first
                if (window._activeSpotifyKey && window._activeSpotifyKey !== key) {
                    const prevContainer = document.getElementById('spe-container-' + window._activeSpotifyKey);
                    const prevFrame     = document.getElementById('spe-frame-'     + window._activeSpotifyKey);
                    const prevPlay      = document.getElementById('spe-icon-play-' + window._activeSpotifyKey);
                    const prevStop      = document.getElementById('spe-icon-stop-' + window._activeSpotifyKey);
                    if (prevContainer) prevContainer.style.display = 'none';
                    if (prevFrame)     prevFrame.src = '';
                    if (prevPlay)      prevPlay.style.display  = '';
                    if (prevStop)      prevStop.style.display  = 'none';
                }

                if (isOpen) {
                    // Close this one
                    container.style.display = 'none';
                    frame.src = '';
                    if (playIcon) playIcon.style.display = '';
                    if (stopIcon) stopIcon.style.display = 'none';
                    window._activeSpotifyKey = null;
                } else {
                    // Open this one
                    frame.src = embedUrl;
                    container.style.display = 'block';
                    if (playIcon) playIcon.style.display = 'none';
                    if (stopIcon) stopIcon.style.display = '';
                    window._activeSpotifyKey = key;
                }
            };
```
*Figure 4.26: Global Iframe Previews Toggle*
