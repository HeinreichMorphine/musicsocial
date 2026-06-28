# Reso Backend Processes Documentation

This document describes the primary backend processes and recommendation engines powering the Reso platform. It maps out the exact source files, classes, methods, and line ranges associated with each architectural phase, clarifying any implementation updates or logic variations within the codebase.

---

## 4.2.1 Authentication & Third-Party OAuth Identity & Scopes

Reso relies on a secure, delegated authentication model to cross-link third-party identity and streaming telemetry with local accounts. The integration uses Laravel Socialite to negotiate authorization handshakes.

### A. Spotify Authorization Redirect & Scopes
The system initiates authorization redirects by requesting specific scopes from the Spotify Account Service. The `user-read-recently-played` scope acts as the foundation for raw stream data scraping, whereas the `playlist-modify` permissions allow the platform to update external assets during synchronization events.

*   **Implementation Location:** [SocialAuthController.php:L16-L39](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L16-L39) (`redirect` method)
*   **Scopes Requested:**
    *   `user-read-email` / `user-read-private` (Identity baselines)
    *   `user-read-recently-played` (Scraping telemetry logs)
    *   `playlist-read-private` / `playlist-read-collaborative` (Fetching playlists)
    *   `playlist-modify-public` / `playlist-modify-private` (Creating and updating playlists)
    *   `streaming` / `user-read-playback-state` / `user-modify-playback-state` (Web playback integration)

### B. On-Demand Token Refresh (Updates Figure 4.2)
> [!IMPORTANT]
> **On-Demand Execution:** Rather than running an asynchronous background worker loop continuously, Reso executes Spotify access token refreshes **on-demand** upon API request failures. If any call to Spotify API endpoints returns a `401 Unauthorized` status (indicating an expired bearer token), the service automatically catches the response and requests a new token using the user's decrypted `spotify_refresh_token`.

*   **Logic Handler:** [SpotifyService.php:L469-L502](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L464-L502) (`refreshUserToken` method)
*   **On-Demand Catch Cases:**
    *   `getUserRecentlyPlayed`: [SpotifyService.php:L438-L447](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L438-L447)
    *   `getUserPlaylists`: [SpotifyService.php:L516-L522](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L516-L522)
    *   `createPlaylist`: [SpotifyService.php:L576-L586](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L576-L586)
    *   `addTrackToPlaylist` / `addTracksToPlaylist`: [SpotifyService.php:L613-L621](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L613-L621), [SpotifyService.php:L653-L661](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L653-L661)

### C. Google OAuth 2.0 Identity Mapping
Google OAuth 2.0 serves as a secondary identity provider, focusing exclusively on identity verification and user verification. This component enforces a stateless verification loop, requesting openid, profile, and email scopes to create a trusted user authentication baseline.

*   **Redirect Logic:** [SocialAuthController.php:L38](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L38)
*   **Callback Mapping:** [SocialAuthController.php:L44-L125](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L44-L125) (`callback` method)
*   **Step-by-Step Resolution Script:** [SocialAuthController.php:L153-L212](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SocialAuthController.php#L153-L212) (`findOrCreateUser` method):
    1.  **Provider Check:** Queries the database by provider unique key (`google_id`). If matched, it updates OAuth tokens and logs the user in.
    2.  **Email Cross-Linking:** If the Google provider ID is not found, the system queries users by `email`. If an email match is found, the accounts are merged: the user's `google_id` is updated, the Google avatar is associated, and the account is marked as verified (`email_verified_at` = `now()`).
    3.  **User Provisioning:** If no record is found, the system provisions a new user record with `email_verified_at` pre-verified by Google.

---

## 4.2.2 Onboarding Initial Profile Construction, External Music API Integration & Genre Sanitization

New platform registrations require a baseline musical configuration to populate recommendations before active tracking logs exist.

```
+-----------------------------------------------------------+
|               User Selection (5-10 songs)                 |
+-----------------------------------------------------------+
                              |
                              v
+-----------------------------------------------------------+
|          [OnboardingController.store]                     |
|  - Clears User's Previous Shelf Songs                     |
|  - Ingests Spotify Tracks Details                         |
|  - Saves to user_shelf_songs (Priority Score = 4.0)       |
|  - Records SongInteraction Liked status                  |
+-----------------------------------------------------------+
```

### A. Initial Profile Construction (Figure 4.4)
The onboarding constructor configures a user profile vector using input selections from onboarding shelves. The selected genre data is grouped and stored in the database, building a synthetic interaction baseline that enables initial recommendation generation.
*   **Logic Handler:** [OnboardingController.php:L48-L89](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L48-L89) (`store` method)
*   **Mandatory Constraint:** The system strictly validates that a user selects between **5 and 10** tracks (Skip is disabled) to build a dense initial profile matrix (TC-07 / Warm Start).
*   **Database Writes:**
    *   Creates records in the `user_shelf_songs` table ([OnboardingController.php:L71-L75](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L71-L75)).
    *   Creates a positive interaction in the `song_interactions` table with the type `like` ([OnboardingController.php:L80-L83](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/OnboardingController.php#L80-L83)) for Discovery page exclusion filtering.

### B. Multi-Source Genre Ingestion & Discogs Fallback (Figure 4.5 & 4.6)
The multi-source ingestion service acts as an abstraction layer for metadata acquisition. It sequentially queries Spotify, Discogs, and MusicBrainz to resolve missing sub-genre tags, merges the heterogeneous results, and routes the output through a sanitization pipeline, caching the completed array for 7 days to conserve API rate limits.
*   **Service Class:** [SpotifyService.php:L210-L417](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L210-L417) (`getGenresWithSources` method)
*   **Cache Lifetime:** 7 Days (`60 * 60 * 24 * 7` seconds, line 213).
*   **Resolution Order (Fallback Chain):**
    1.  **Spotify Artist:** Fetches genres linked to the primary and supporting track artists ([SpotifyService.php:L241-L256](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L241-L256)).
    2.  **Spotify Album:** Retrieves genres linked to the parent album context ([SpotifyService.php:L258-L274](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L258-L274)).
    3.  **Discogs Service Adapter:** Calls the Discogs catalog API to fetch release styles (e.g. mapping "Electronic" sub-genres down to "IDM" or "Acid House") ([DiscogsService.php](file:///c:/laragon/www/musicsocial-main/app/Services/DiscogsService.php)).
    4.  **MusicBrainz Fallback:** (Triggered if genres count is < 3) Queries artist profile tags from the MusicBrainz API ([MusicBrainzService.php](file:///c:/laragon/www/musicsocial-main/app/Services/MusicBrainzService.php)).
    5.  **iTunes Heuristic Search:** (Triggered if genres count is < 3) Dispatches searches to the Apple iTunes Search API to retrieve structured metadata ([SpotifyService.php:L310-L336](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L310-L336)).
    6.  **YouTube Tags Fallback:** Dispatches searches to the YouTube API and reads video tagging contexts ([SpotifyService.php:L338-L357](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L338-L357)).
    7.  **Contextual Playlist Search:** Scans titles and descriptions of matching public Spotify playlists to extract genres textually ([SpotifyService.php:L359-L388](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L359-L388)).
    8.  **Database Sibling Track Inheritance:** Pulls genres from previously indexed songs by the same artist in the database ([SpotifyService.php:L398-L411](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L398-L411)).

### C. Genre Sanitization Pipeline (Figure 4.7)
The `GenreCleanerService` normalizes unstructured string inputs from third-party APIs.
*   **Service Class:** [GenreCleanerService.php:L140-L191](file:///c:/laragon/www/musicsocial-main/app/Services/GenreCleanerService.php#L140-L191) (`clean` method)
*   **Pipeline Logic:**
    1.  **Blocklist Filtering:** Drops subjective, non-genre user tags (e.g., `seen live`, `favorites`, `female vocalists`, `under 2000 listeners`) defined in `GenreCleanerService::$blocklist` ([GenreCleanerService.php:L14-L28](file:///c:/laragon/www/musicsocial-main/app/Services/GenreCleanerService.php#L14-L28)).
    2.  **Alias Mapping:** Normalizes synonymous phrases and formats via `GenreCleanerService::$aliases` (user manual overrides).
    3.  **Beets Whitelist Normalization:** Cross-references entries against a trusted canonical list loaded from `genres.txt` and `storage/app/genres/musicbrainz_genres.json` (lines 54-84). Strict mode eliminates any genre not in the whitelist.
    4.  **Formatting & Deduplication:** Cleans strings of numeric decade or date formatting, strips short strings, and removes redundant entries to yield a clean metadata set.

---

## 4.2.3 Feature Vectorization (Cold Start Processing)

The Python recommendation engine processes onboarding selections into a standardized feature string, which is passed through a TF-IDF vectorizer to calculate the weight of each attribute.

### A. Song Feature Building (Figure 4.8)
*   **Implementation Location:** [app.py:L975-L1012](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L975-L1012) (`build_song_features` function)
*   **Features Representation:**
    *   The artist's name is normalized, spaces are replaced with underscores (e.g. `harry_styles`), and it is **repeated 2x** to weight the artist signature twice as high as genres.
    *   Genre names are similarly normalized and appended to the space-separated text token block.
*   **Example Output:** `"harry_styles harry_styles pop indie_pop dance"`

### B. Rare Genre Logarithmic Boost (Warm Start Baseline)
The core strength of the Warm Start Baseline is the logarithmic boost given to rare genres. The TF-IDF formula ensures that if a user selects a rare genre during onboarding, that specific affinity is weighted significantly higher than a generic selection.
*   **Implementation Location:** [app.py:L374-L410](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L374-L410) (Audit mathematical proof)
*   **Mathematical Concept:**
    $$w_i = \log_{10}\left(\frac{N}{\text{df}_i}\right)$$
    Where $N$ is the total track catalog size and $\text{df}_i$ is the document frequency of genre $i$. Niche genres (low $\text{df}_i$) yield a significantly higher Inverse Document Frequency (IDF) weight than generic genres, surface nuance immediately, and prevent popular recommendation bubbles.

---

## 4.2.4 Content Similarity Matching

The similarity matching routine executes a cosine distance computation between the target user vector and the global song matrix to evaluate overlapping directional components between data objects in a high-dimensional vector space.

*   **Implementation Location:** [app.py:L1013-L1120](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1013-L1120) (`content_based_similarity_tfidf` function)
*   **Mathematical Model:**
    $$\text{cos}(\theta) = \frac{A \cdot B}{\|A\| \|B\|}$$
    Where $A$ is the user preference vector (mathematical mean centroid along `axis=0` of the user's liked/interacted track TF-IDF features matrix, line 1072) and $B$ is the candidate track vector.
*   **Warm Start Phase Trigger:** When a user has between **5 and 9** effective interactions (calculated as `max(num_interactions, shelf_songs)` in lines 1336–1337), the backend bypasses global fallbacks and runs the TF-IDF Similarity engine.
*   **String-Based Fallback Model:** If the sparse matrix calculation fails or returns empty lists, the engine falls back to a direct string-based Jaccard similarity match ([app.py:L1121-L1166](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1121-L1166), `content_based_similarity` function) mapping artist overlap and genre intersection sets.
*   **Protected Buffer Optimization:** Rather than outputting a small target array, the recommendation microservice outputs the **top 50** candidate records ([app.py:L1700](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1700)). This prevents candidate starvation at the Laravel API layer: after Laravel filters out tracks the user has already liked, bookmarked, or skipped in their active session, a high-quality selection remains available.

---

## 4.2.5 Interaction Weighting & SVD Model Training

To track passive user actions over time, continuous interaction metrics are transformed into bounded preference scores and processed using matrix decomposition.

### A. Database Signal Weighting (Figure 4.10)
The system fetches interactions from various relational tables and weights them according to engagement intensity:
*   **Shelf Add:** 4.0 Points (Premium Identity Signal)
*   **Share/Post:** 3.0 Points (Strong Content Creation Signal)
*   **Comment Suggestion (`[SONG:xyz]`):** 3.0 Points
*   **Comment Text:** 1.0 Point
*   **Playlist Add:** 2.0 Points
*   **Like / Discovery Like:** 2.0 Points
*   **Listen / Discovery Listen:** 1.0 Point
*   **Dislike / Discovery Dislike:** -1.0 Point (Strong Negative Signal)

*   **Implementation Location:** [app.py:L124-L200](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L124-L200) (`fetch_data_from_db` function)

### B. Score Log-Flattening Algorithm
A log-flattening algorithm normalizes highly active users and prevents system distortion. The raw interaction score is converted into a structured preference indicator, normalized against a set rating scale.
*   **Implementation Location:** [app.py:L210-L215](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L210-L215)
*   **Log-Flattening Formula:**
    $$c_{ui} = 1 + \ln(1 + r_{ui})$$
    Where $r_{ui}$ is the sum of engagement points for user $u$ on song $i$. This dampens extreme outliers. Dislikes are passed as a raw $-1.0$ indicator.

### C. Surprise SVD Model Training
The model is trained over 20 iterations using stochastic gradient descent.
*   **Implementation Location:** [app.py:L253-L287](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L253-L287) (`train_and_save_model` function)
*   **Training Configuration:**
    *   SVD algorithm initialized with `n_epochs=20`, learning rate `lr_all=0.005`, and regularization parameter `reg_all=0.02` (line 274).
    *   Rating scale set to `(-1, 6)` to capture negatives and flattened metrics (line 268).

---

## 4.2.6 Social Trust Boosting

Rather than calculating recommendations in isolation, the platform uses an intentional mathematical bias to prioritize tracking telemetry from verified social circles.

```
                                +-------------------+
                                |    Social Graph   |
                                |  - Follows (0.8)  |
                                |  - Peers   (1.0)  |
                                +-------------------+
                                          |
                                          v
+------------------+           +--------------------+           +----------------------+
|  Base SVD Score  | * 0.7  +  | Trust-Weighted Log | * 0.3  =  | Final Recommendation |
|   (Algorithmic)  |           |   (Social Circle)  |           |    Hybrid Score      |
+------------------+           +--------------------+           +----------------------+
```

### A. Power Log Trust Calculation
The social weight utility uses a logarithmic ratio calculation to evaluate account interactions.
*   **Implementation Location:** [app.py:L873-L916](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L873-L916) (`calculate_trust` function)
*   **Mathematical Formula:**
    $$t(u_a, u_i) = \frac{\ln(1 + |F_{sharer}|^{0.7})}{1 + 0.5 \cdot \ln(1 + |F_{active}|)}$$
    Where:
    *   $|F_{sharer}|$ is the number of followers the sharing user has (representing their baseline popularity/influence, dampened by exponent $0.7$).
    *   $|F_{active}|$ is the number of accounts the active user follows (representing active follow dilution).

### B. Relationship Multiplier ($R_m$)
The trust score is scaled by a relationship proximity weight:
*   **Playlist Peer-Node (Mutual Collaborative Playlist Member):** $R_m = 1.0$ (deliberate active collaboration)
*   **Followed User:** $R_m = 0.8$ (unidirectional interest)
*   **Global Community Member (Stranger):** $R_m = 0.3$

*   **Logic Handler:** [app.py:L736-L779](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L736-L779) (`get_social_graph` method) and [app.py:L1631-L1641](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1631-L1641)

---

## 4.2.7 Dynamic Preference Boosting & Identity Aggregation

The platform constructs a user's operational "Identity Vector" by gathering cross-table engagement metrics.

### A. Identity Vector Aggregation
This routine compiles all distinct artist names linked to a specific user across implicit social logs, explicit posts, onboarding selections, and shared playlists.
*   **Implementation Location:** [app.py:L1235-L1260](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1235-L1260) (`get_liked_artists` function)
*   **Database Aggregation (SQL UNION):**
    ```sql
    SELECT DISTINCT so.artist_name FROM likes l JOIN shares s ON l.share_id = s.id JOIN songs so ON s.song_id = so.id WHERE l.user_id = :user_id
    UNION
    SELECT DISTINCT so.artist_name FROM shares s JOIN songs so ON s.song_id = so.id WHERE s.user_id = :user_id
    UNION
    SELECT DISTINCT so.artist_name FROM user_shelf_songs uss JOIN songs so ON uss.song_id = so.spotify_track_id WHERE uss.user_id = :user_id
    UNION
    SELECT DISTINCT so.artist_name FROM playlist_songs ps JOIN songs so ON ps.song_id = so.spotify_track_id WHERE ps.added_by_user_id = :user_id
    ```
*   **Sanitization:** The returned dataset is transformed into a sanitized, lower-case Python set to eliminate redundant database calls during high-throughput similarity filtering.

### B. Explicit Artist Preference Boosting
*   **Implementation Location:** [app.py:L1351-L1359](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1351-L1359) (Within recommendations routing)
*   **Contextual Boost:** If the candidate track's artist matches a string in the user's compiled identity set, the system applies an intentional **additive modifier of 0.4** directly to the baseline prediction score, ensuring local artist affinity takes precedence over global trends.

---

## 4.2.8 Recommendation Tiered Fallback Safeties

> [!NOTE]
> **Safety Net Pipeline:** When a user's recommendation pool is sparse (contains fewer than 12 recommendations), the service triggers a tiered fallback pipeline to fill candidate slots, preventing user feed starvation.

*   **Implementation Location:** [app.py:L1471-L1565](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L1471-L1565) (Within recommendations routing)
*   **Fallback Progression:**
    1.  **Tier 1: Genre-Aware Popularity Fallback:** Queries popular songs in the database matching the user's liked genres list (`genres LIKE '%"genre"%'`), returning them with a baseline score of `0.15` and the reasoning `"Vibe match: Based on your genre favorites"`.
    2.  **Tier 2: Global Popularity Fallback:** Returns globally popular songs based on database likes and shares count, returning them with a baseline score of `0.10` and the reasoning `"Trending in the community"`.
    3.  **Tier 3: Absolute Random Fallback:** If database errors occur or popularity calculations yield empty sets, the system performs a random selection of tracks (`ORDER BY RAND() LIMIT 12`) labeled `"Discovered for you"` with a baseline score of `0.05`.

---

## 4.2.9 Vector Identity Transformation

For users with an active listening history, the recommendation service maps textual attributes into a continuous coordinate matrix, compressing the user’s multifaceted historical interactions into a singular mathematical centroid representing their musical identity.

*   **Implementation Location:** [app.py:L438-L468](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L438-L468) (Within audit calculations)
*   **centroid Mapping:**
    1.  Fetches user history (likes, shares, shelf songs, playlist songs) and extracts genre and artist features.
    2.  Converts the collection of feature blocks into a sparse coordinate weight matrix using the cached `TfidfVectorizer` ([app.py:L446](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L446)).
    3.  Computes the mathematical mean along the vertical axis (`axis=0`) ([app.py:L447](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L447)):
        $$\text{UserVector} = \text{mean}(\text{FeatureMatrix}, \text{axis}=0)$$
    4.  The output coordinate vector is compared directly against candidate tracks to yield the cosine similarity score.

---

## 4.2.10 Feed Generation & Injection

The web platform blends chronological social interactions with algorithmically injected items to construct personalized dashboard interfaces.

*   **Laravel Controller:** [FeedController.php:L41-L128](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L41-L128) (`index` method)
*   **Feed Construction Steps:**
    1.  **Eloquent Index Scan:** Retrieves the latest 20 social posts (`Share` models) from users the current user follows (including their own posts), using pagination ([FeedController.php:L59-L68](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L59-L68)).
    2.  **Out-of-Process Recsys Call:** dispatches an HTTP request to the Flask recommender service: `GET /recommendations/{userId}` ([FeedController.php:L71](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L71)).
    3.  **Collection Hydration:** Matches recommender-returned IDs back into full Eloquent database models, sorting recommendations by score, attaching reasons, and filtering out songs user has explicitly disliked ([FeedController.php:L75-L110](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L75-L110)).
    4.  **Blade Rendering:** Merges social posts, suggested users to follow, and recommended tracks, returning the data arrays to the `dashboard` view layer for user injection.

---

## 4.2.11 Collaborative Authorization Enforcement

Group playlist permissions are protected by strict relational gateway rules.

*   **Implementation Locations:**
    *   **Proximity Limitation:** [PlaylistController.php:L97-L98](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L97-L98) (`$user->friends()` method maps mutual followers)
    *   **Relational Gateway Checks:** [PlaylistController.php:L186-L189](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L186-L189) (`addSong` method) and [PlaylistController.php:L317-L324](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L317-L324) (`removeSong` method)
*   **Enforcement Mechanics:**
    1.  **UI Restriction:** Users can only invite "Friends" to collaborate, where friends are defined as mutual followers:
        ```php
        // User.php friends() relation
        return $this->following()->whereIn('user_id', $this->followers()->pluck('follower_id'));
        ```
    2.  **Gateway Check:** Relational controllers verify that a user possesses an `accepted` status inside the `playlist_collaborators` pivot table before modifying playlist songs or metadata (e.g. throwing `403 Forbidden` if missing).
    3.  **Removal Authorization:** Deleting songs from a playlist is restricted to the specific contributor who added the track or the playlist owner ([PlaylistController.php:L316-L324](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistController.php#L316-L324)).

---

## 4.2.12 Threaded Comment Resolution Logic

To support nested community conversations without incurring heavy database overhead, the social feed architecture uses eager relationship matching.

*   **Controller Handler:** [ShareController.php:L230-L254](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/ShareController.php#L230-L254) (`show` method)
*   **Eager Relationship Loading:**
    To avoid N+1 query patterns when loading nested conversations, the comment query isolates root-level comments (comments where parent ID is null) and eager-loads child replies down multiple tree layers using Eloquent relationships:
    ```php
    $commentsQuery = $share->comments()
        ->whereDoesntHave('parent')
        ->with(['user', 'replies.user', 'replies.replies.user']);
    ```
    This structures full conversational hierarchies in a single database operation before rendering the view.

---

## 4.2.13 In-Text Upvoting, Mention Notifications, & Recursive Reply Cleanup

> [!NOTE]
> **Advanced Social Comments Processing:** Social commenting features include in-text upvoting, regex-based user mentions notifications, and automated cascade cleanups of orphaned parent comments.

### A. In-Text Comment Upvoting
Instead of maintaining a separate pivot table for comment upvotes, user upvote states are stored serialized inside the comment body itself as an `[UPVOTES:id1,id2,...]` tag.
*   **Logic Handler:** [CommentController.php:L239-L266](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L239-L266) (`toggleUpvote` method)
*   **Tag Mechanics:** Regular expression checks locate the `[UPVOTES:...` tag, parse the comma-separated IDs list, append or remove the active user ID, and update the comment text block dynamically.

### B. Regex User Mention Notifications
*   **Logic Handler:** [CommentController.php:L92-L111](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L92-L111) (Within the `store` method)
*   **Regex Pattern:** `/@([\w\.\-]+)/` extracts mentioned usernames from comment bodies. Matches are resolved to User models, dispatching a `UserMentionedNotification` to each user.

### C. Recursive Orphaned Comment Cleanup
To preserve thread structure when users delete replies, Reso employs a hybrid soft/hard deletion strategy.
*   **Logic Handler:** [CommentController.php:L192-L234](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/CommentController.php#L192-L234) (`destroy` and `cleanupParent` methods)
*   **Deletion Protocol:**
    1.  **Soft-Redact:** If a deleted comment has child replies, its body is updated to `"[deleted]"` to preserve the reply tree (lines 193-198).
    2.  **Hard-Delete & Cascade:** If it has no replies, it is permanently deleted. The controller then recursively checks parent comments. If a parent comment has a `"[deleted]"` body and now has `0` child replies left, the parent comment is hard-deleted, cleaning up orphaned threads up the conversation chain (lines 206-233).

---

## 4.2.14 Behavioral Auditing

User behavior logs are collected by background audit workers to continuously enrich the machine learning pipelines.

*   **Logic Handler:** [SongInteractionController.php:L14-L47](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SongInteractionController.php#L14-L47) (`store` method)
*   **Engagement Tracking:** When interactions (like, listen, dislike) occur in the UI (e.g. passing or liking on the Discovery page), the backend creates or updates a record in the `song_interactions` table.
*   **Cache Invalidation:** The interaction class immediately clears the recommended songs cache for that user (`Cache::forget("user_{$user->id}_recommended_songs")`, line 41) to ensure they receive refreshed recommendations on their next dashboard visit.

---

## 4.2.15 External Library Export (Spotify Synchronization)

The platform enables users to manifest local database discoveries into external playlists.

*   **Controller Handler:** [PlaylistExportController.php:L22-L86](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistExportController.php#L22-L86) (`export` method)
*   **Export Formatting:**
    *   Iterates through bookmarked or recommended tracks to build a list of Spotify track IDs.
    *   Concatenates track IDs with the Spotify URI protocol prefix ([PlaylistExportController.php:L61](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/PlaylistExportController.php#L61)):
        $$\text{SpotifyURI} = \text{"spotify:track:"} + \text{song.spotify\_track\_id}$$
    *   Dispatches the list to Spotify APIs in a single batch request using `addTracksToPlaylist()` (which chunks arrays into batches of 100 to avoid API length constraints, [SpotifyService.php:L639-L670](file:///c:/laragon/www/musicsocial-main/app/Services/SpotifyService.php#L639-L670)), avoiding the performance penalties of sequential API calls.

---

## 4.2.16 External Playlist Import & Ingestion Mechanics

To allow users to bring existing musical contexts onto the platform, the backend implements a multi-stage ingestion routine.

### A. Regex URL Playlist ID Extraction (Figure 4.20)
*   **Logic Handler:** [SpotifyImportController.php:L56-L75](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L56-L75) (`preview` method)
*   **Regex Pattern:**
    ```php
    preg_match('/playlist\/([a-zA-Z0-9]+)/', $url, $matches)
    ```
    Isolates the unique alphanumeric playlist ID from the Spotify share link. The ID is sent to `SpotifyService` to fetch remote track information (name, artists, and album art).

### B. Playlist Ingestion Transaction Loop & Hard Cap (Figure 4.21)
*   **Logic Handler:** [SpotifyImportController.php:L126-L188](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L126-L188) (`process` method)
*   **Import Constraints & Logic:**
    1.  **Strict 15-Track Limit:** The validation rules enforce a **hard cap of 15 selected tracks** per import event ([SpotifyImportController.php:L131](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L131)).
    2.  **Database Transaction:** Ingests songs within a database transaction ([SpotifyImportController.php:L155](file:///c:/laragon/www/musicsocial-main/app/SpotifyImportController.php#L155)).
    3.  **Song Deduplication (`firstOrCreate`):** For each selected track, it checks for a localized footprint:
        ```php
        Song::firstOrCreate(
            ['spotify_track_id' => $trackData['id']],
            [
                'track_name' => $trackData['name'],
                'artist_name' => $trackData['artist'],
                'album_art_url' => $trackData['album_art'],
                'spotify_url' => 'https://open.spotify.com/track/' . $trackData['id']
            ]
        );
        ```
    4.  **Collaborative Linkage:** Creates the relationship in the `playlist_songs` table with `added_by_user_id` mapped to the current contributor ([SpotifyImportController.php:L172-L177](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/SpotifyImportController.php#L172-L177)), preventing database duplicates.
