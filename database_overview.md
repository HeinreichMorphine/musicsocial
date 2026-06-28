# Reso Database Overview

This document provides a technical overview of the Reso database architecture, focusing on the dual-layer storage design and critical queries implementing the platform's social features and recommendation pipelines.

---

## 4.3 Database Architecture

The Reso platform utilizes a dual-layer data architecture optimized for both transactional social interactions and intensive recommendation calculations:
1. **MySQL Relational Database**: Handles user account management, OAuth identities, playlists, posts (shares), threaded comments, relationships, and user engagement configurations.
2. **SQLAlchemy & PyMySQL Bridge**: Used by the Python recommendation microservice to run read-only analytical queries and map user-item feedback vectors straight into Pandas DataFrames for matrix factorization training.

---

## 4.3.4 SQL Queries and Functions

This section details key SQL and Eloquent queries that represent the database's netcentric capabilities. These queries handle Spotify Web API data synchronization, Taste Twin music compatibility matching, real-time collaborative playlist synchronization, multi-source hybrid recommendation modeling, and behavioral song engagement logging. All queries are optimized for high performance under load using composite indexes, parameterized statements, and strategic eager loading to prevent $N+1$ problems.

---

### Eager-Loaded Social Feed Generation

This query fetches shares posted by the user and the accounts they follow, sorting entries chronologically. It uses Eloquent's eager-loading capabilities (`with`) to prevent performance-degrading $N+1$ loop patterns by bundling related user, song, and like data into a single query execution.

* **Source:** [FeedController.php (L54-L64)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/FeedController.php#L54-L64)

```php
            $followingIds = $user->following()->pluck('id');
            $shares = Share::where('is_deleted', false)
                           ->where(function ($query) use ($followingIds, $user) {
                               $query->whereIn('user_id', $followingIds)
                                     ->orWhere('user_id', $user->id);
                           })
                           ->with(['user', 'song', 'likes'])
                           ->latest()
                           ->paginate(20);
```
*Figure 4.27: Social Feed Query with Eager Loading (Eloquent)*

---

### Profile Interest Aggregator (Top Genre Discovery)

This internal utility extracts profile genre data to dynamically render account identity badges. The query executes a database inner join between `shares` and `songs` to pull all raw genre array strings belonging to user posts, formatting the results into a clean text matrix for frequency calculation.

* **Source:** [UserProfileController.php (L194-L198)](file:///c:/laragon/www/musicsocial-main/app/Http/Controllers/UserProfileController.php#L194-L198)

```php
         $sharedGenres = DB::table('shares')
            ->join('songs', 'shares.song_id', '=', 'songs.id')
            ->where('shares.user_id', $user->id)
            ->pluck('songs.genres')
            ->toArray();
```
*Figure 4.28: Profile Interest Aggregator (Top Genre Discovery)*

---

### Peer-Node Social Graph Extraction

Executed by the recommendation microservice via SQLAlchemy to map playlist connection vectors. By isolating distinct users who share an active, accepted collaborative playlist entry with the source profile, the script builds a high-trust affinity graph used to adjust scoring weights.

* **Source:** [app.py (L761-L769)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L761-L769)

```python
    peer_query = text("""
        SELECT DISTINCT pc2.user_id
        FROM playlist_collaborators pc1
        JOIN playlist_collaborators pc2 ON pc1.playlist_id = pc2.playlist_id
        WHERE pc1.user_id = :user_id
          AND pc1.status = 'accepted'
          AND pc2.status = 'accepted'
          AND pc2.user_id != :user_id
    """)
```
*Figure 4.29: Peer-Node Social Graph Extraction (SQLAlchemy)*

---

### Multi-Source Interaction Extraction & Aggregation

To collect user-item feedback vectors for training the SVD matrix factorization engine, the Python analytics script queries interaction variables from separate datastores, assigning explicit mathematical reward weights to each distinct action type.

* **Source:** [app.py (L133-L178)](file:///c:/laragon/www/musicsocial-main/recommender_service/app.py#L133-L178)

```python
            # 1. Likes (2.0 Points)
            likes_query = "SELECT l.user_id, s.song_id, 2.0 as score FROM likes l JOIN shares s ON l.share_id = s.id"
            likes_df = pd.read_sql(likes_query, connection)

            # 2. Shares/Posts (3.0 Points)
            shares_query = "SELECT user_id, song_id, 3.0 as score FROM shares"
            shares_df = pd.read_sql(shares_query, connection)
            
            # 3. Comments & Song Suggestions (Standard: 1.0 Point, Suggestions: 3.0 Points)
            comments_query = """
                SELECT c.user_id, 
                       COALESCE(so.id, s.song_id) as song_id,
                       CASE WHEN c.body LIKE '%%[SONG:%%' THEN 3.0 ELSE 1.0 END as score,
                       c.id as comment_id
                FROM comments c
                JOIN shares s ON c.share_id = s.id
                LEFT JOIN songs so ON c.body LIKE CONCAT('%%[SONG:', so.spotify_track_id, ']%%')
            """
            comments_df = pd.read_sql(comments_query, connection)

            # 4. Playlist Adds (2.0 Points)
            playlist_query = """
                SELECT ps.added_by_user_id as user_id, so.id as song_id, 2.0 as score
                FROM playlist_songs ps
                JOIN songs so ON ps.song_id = so.spotify_track_id
                JOIN playlist_collaborators pc ON pc.playlist_id = ps.playlist_id
                    AND pc.user_id = ps.added_by_user_id AND pc.status = 'accepted'
            """
            playlist_df = pd.read_sql(playlist_query, connection)

            # 5. Profile Shelf Adds (4.0 Points)
            shelf_query = """
                SELECT uss.user_id, so.id as song_id, 4.0 as score
                FROM user_shelf_songs uss
                JOIN songs so ON uss.song_id = so.spotify_track_id
            """
            shelf_df = pd.read_sql(shelf_query, connection)
```
*Figure 4.30: Multi-Source Interaction Extraction & Aggregation*
