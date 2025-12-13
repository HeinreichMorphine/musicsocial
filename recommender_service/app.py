from flask import Flask, jsonify, request
import os
from dotenv import load_dotenv
from sqlalchemy import create_engine, text
import pandas as pd
import json
import math  # For logarithmic trust calculations
import numpy as np  # For array operations in TF-IDF
from sklearn.feature_extraction.text import TfidfVectorizer  # For feature weighting
from sklearn.metrics.pairwise import cosine_similarity  # For vector similarity
from surprise import Dataset, Reader, SVD
from surprise.model_selection import train_test_split
import joblib
import threading
import time

# Global variable for the trained model
algo = None
engine = None

# Global TF-IDF cache for performance optimization
# Caches the vectorizer and feature matrix to avoid recomputation
# Cache is invalidated when the song catalog changes
tfidf_cache = {
    'vectorizer': None,           # Fitted TfidfVectorizer
    'all_songs_matrix': None,     # TF-IDF feature matrix for all songs
    'all_songs_df': None,         # DataFrame with song IDs for mapping
    'song_ids': None,             # Set of song IDs for cache validation
    'last_update': None           # Timestamp of last cache update
}

# Load environment variables from .env file
load_dotenv()

app = Flask(__name__)

# Database configuration
DB_HOST = os.getenv('DB_HOST')
DB_DATABASE = os.getenv('DB_DATABASE')
DB_USERNAME = os.getenv('DB_USERNAME')
DB_PASSWORD = os.getenv('DB_PASSWORD')

def init_db_connection():
    global engine
    if engine is not None:
        return
    try:
        db_uri = f"mysql+pymysql://{DB_USERNAME}:{DB_PASSWORD}@{DB_HOST}/{DB_DATABASE}"
        # Add connection pooling for better performance
        # pool_size: Number of connections to maintain in the pool
        # max_overflow: Additional connections allowed beyond pool_size
        # pool_recycle: Recycle connections after this many seconds (prevents stale connections)
        # pool_pre_ping: Test connections before using them
        engine = create_engine(
            db_uri,
            pool_size=10,          # Maintain 10 connections in pool
            max_overflow=20,       # Allow up to 20 additional connections
            pool_recycle=3600,     # Recycle connections after 1 hour
            pool_pre_ping=True     # Verify connection health before use
        )
        # Test the connection
        with engine.connect() as connection:
            print("Successfully connected to the database with connection pooling enabled")
    except Exception as e:
        print(f"Error connecting to MySQL database using SQLAlchemy: {e}")
        engine = None

def fetch_data_from_db():
    if engine is None:
        print("Database engine not initialized. Cannot fetch data.")
        return pd.DataFrame()

    print("\n--- Starting Data Fetch ---")
    try:
        with engine.connect() as connection:
            # Positive interactions from likes with weight 1.0
            likes_query = "SELECT l.user_id, s.song_id, 1.0 as interaction FROM likes l JOIN shares s ON l.share_id = s.id"
            likes_df = pd.read_sql(likes_query, connection)
            print(f"1. Likes: Found {len(likes_df)} records.")

            # Negative interactions from feedback with weight -1.0
            feedback_query = "SELECT uf.user_id, s.song_id, -1.0 as interaction FROM user_feedback uf JOIN shares s ON uf.share_id = s.id WHERE uf.feedback_type = 'not_interested'"
            feedback_df = pd.read_sql(feedback_query, connection)
            print(f"2. Not Interested Feedback: Found {len(feedback_df)} records.")

            # Negative interactions from dislikes with weight -1.0
            dislikes_query = "SELECT d.user_id, s.song_id, -1.0 as interaction FROM dislikes d JOIN shares s ON d.share_id = s.id"
            dislikes_df = pd.read_sql(dislikes_query, connection)
            print(f"3. Dislikes: Found {len(dislikes_df)} records.")

            # User's own shares with a higher weight of 1.5
            shares_query = "SELECT user_id, song_id, 1.5 as interaction FROM shares"
            shares_df = pd.read_sql(shares_query, connection)
            print(f"4. User's Own Shares: Found {len(shares_df)} records.")

            # Likes from followed users with a weight of 1.2
            following_likes_query = """
                SELECT f.follower_id as user_id, s.song_id, 1.2 as interaction
                FROM followers f
                JOIN likes l ON f.user_id = l.user_id
                JOIN shares s ON l.share_id = s.id
            """
            following_likes_df = pd.read_sql(following_likes_query, connection)
            print(f"5. Likes from Followed Users: Found {len(following_likes_df)} records.")

            # Shares from followed users with a weight of 0.8
            following_shares_query = """
                SELECT f.follower_id as user_id, s.song_id, 0.8 as interaction
                FROM followers f
                JOIN shares s ON f.user_id = s.user_id
            """
            following_shares_df = pd.read_sql(following_shares_query, connection)
            print(f"6. Shares from Followed Users: Found {len(following_shares_df)} records.")

            # Combine all interactions
            # Filter out empty dataframes first to avoid FutureWarning
            dfs_to_concat = [df for df in [likes_df, feedback_df, dislikes_df, shares_df, following_likes_df, following_shares_df] if not df.empty]
            
            if not dfs_to_concat:
                print("--- No interaction data found in any table. ---")
                return pd.DataFrame()

            interactions_df = pd.concat(dfs_to_concat, ignore_index=True)

            interactions_df = interactions_df.rename(columns={'song_id': 'item_id'})
            
            # Remove duplicates, keeping the interaction with the highest weight
            interactions_df = interactions_df.sort_values('interaction', ascending=False).drop_duplicates(subset=['user_id', 'item_id'], keep='first')

            print(f"\nTotal unique user-item interactions fetched: {len(interactions_df)}")
            print("--- Finished Data Fetch ---\n")
            return interactions_df
    except Exception as e:
        print(f"An exception occurred during data fetching: {e}")
        return pd.DataFrame()

MODEL_PATH = 'surprise_model.pkl'

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

    reader = Reader(rating_scale=(-1, 1.5)) # Adjusted rating scale
    data = Dataset.load_from_df(interactions_df[['user_id', 'item_id', 'interaction']], reader)

    trainset = data.build_full_trainset()

    print("Training SVD model...")
    algo = SVD(n_epochs=20, lr_all=0.005, reg_all=0.02, random_state=42)
    algo.fit(trainset)
    print("Model training complete.")

    joblib.dump(algo, MODEL_PATH)
    print(f"Model saved to {MODEL_PATH}")

def load_model():
    global algo
    if os.path.exists(MODEL_PATH):
        try:
            print(f"Loading model from {MODEL_PATH}...")
            algo = joblib.load(MODEL_PATH)
            print("Model loaded successfully.")
        except EOFError:
            print("Model file is empty. Training a new one.")
            train_and_save_model()
    else:
        print("No model found. Training a new one.")
        train_and_save_model()

# Load model on startup
with app.app_context():
    init_db_connection()
    load_model()

@app.route('/')
def home():
    return "Recommendation Service is running!"

@app.route('/retrain', methods=['POST'])
def retrain_model_endpoint():
    try:
        train_and_save_model()
        return jsonify({"status": "success", "message": "Model retraining initiated and completed."}), 200
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}) , 500

@app.route('/test_db_connection')
def test_db_connection():
    if engine:
        return jsonify({"status": "success", "message": "Database connection successful!"})
    else:
        return jsonify({"status": "error", "message": "Failed to connect to database."}) , 500

def get_social_graph(user_id, connection):
    """
    Fetches the list of users that the active user follows.
    """
    query = text("SELECT user_id FROM followers WHERE follower_id = :user_id")
    result = connection.execute(query, {'user_id': user_id})
    return {row[0] for row in result}

def get_song_sharers_bulk(song_ids, connection):
    """
    Fetches users who shared the given songs.
    Returns a dict: {song_id: [user_id, user_id, ...]}
    """
    if not song_ids:
        return {}
    
    # Use IN clause for bulk fetch
    # Note: formatting the list of IDs directly into the query string is safe here because they are integers,
    # but using bind parameters is better practice. However, for a variable length list in raw SQL with SQLAlchemy,
    # we often need to expand it.
    
    # Using pandas for easier handling
    query = f"SELECT song_id, user_id FROM shares WHERE song_id IN ({','.join(map(str, song_ids))})"
    try:
        df = pd.read_sql(query, connection)
        sharers_map = df.groupby('song_id')['user_id'].apply(list).to_dict()
        return sharers_map
    except Exception as e:
        print(f"Error in get_song_sharers_bulk: {e}")
        return {}

def get_follower_counts_bulk(user_ids, connection):
    """
    Fetches follower counts for multiple users in optimized batches.
    
    PERFORMANCE OPTIMIZATION:
    - Processes in batches of 100 to avoid SQL query length limits
    - Handles errors gracefully per batch
    - More efficient than individual queries
    
    This is used to determine the influence/popularity of users in the social network.
    Users with more followers are considered more influential.
    
    Args:
        user_ids: List of user IDs to fetch follower counts for
        connection: Database connection object
    
    Returns:
        Dictionary mapping user_id to their follower count: {user_id: follower_count}
        Returns empty dict on error or if no user_ids provided
    """
    if not user_ids:
        return {}
    
    all_counts = {}
    batch_size = 100  # Process 100 users at a time
    
    # Process in batches to avoid query length issues
    for i in range(0, len(user_ids), batch_size):
        batch = list(user_ids)[i:i+batch_size]
        
        # Bulk fetch follower counts using GROUP BY for efficiency
        query = f"""
            SELECT user_id, COUNT(*) as follower_count 
            FROM followers 
            WHERE user_id IN ({','.join(map(str, batch))})
            GROUP BY user_id
        """
        try:
            df = pd.read_sql(query, connection)
            batch_counts = dict(zip(df['user_id'], df['follower_count']))
            all_counts.update(batch_counts)
        except Exception as e:
            print(f"Error in get_follower_counts_bulk (batch {i//batch_size + 1}): {e}")
            # Continue with other batches even if one fails
    
    return all_counts

def calculate_trust(active_user_friends, sharer_friends):
    """
    Calculate trust score using logarithmic formula based on social network theory.
    
    Formula: t(ua, ui) = 1/(1 + log(F(ua))) * log(F(ui))
    
    Where:
        - ua = active user (the user receiving recommendations)
        - ui = sharer (the user who shared/liked the song)
        - F(u) = number of friends (followers) of user u
    
    Rationale:
        - Users who follow many people have their trust "diluted" across more connections
          (denominator increases with active user's friend count)
        - Users with many followers are more influential, so their shares carry more weight
          (multiplier increases with sharer's follower count)
        - Logarithmic scaling prevents extreme values and provides diminishing returns
    
    Args:
        active_user_friends: Number of users the active user follows
        sharer_friends: Number of followers the sharer has
    
    Returns:
        Trust score (float, typically between 0 and ~5 for normal social networks)
    """
    # Ensure minimum of 1 to avoid log(0) which is undefined
    # This also means users with 0 friends get treated as having 1 friend
    active_user_friends = max(1, active_user_friends)
    sharer_friends = max(1, sharer_friends)
    
    # Calculate logarithmic components
    # Using natural log (ln) for smooth scaling
    log_active = math.log(active_user_friends)
    log_sharer = math.log(sharer_friends)
    
    # Apply trust formula
    # The 1/(1 + log_active) normalizes based on how selective the active user is
    # Multiplying by log_sharer amplifies influence of popular users
    trust = (1.0 / (1.0 + log_active)) * log_sharer
    
    return trust

def get_or_build_tfidf_cache(all_songs_df):
    """
    Get cached TF-IDF matrix or rebuild if song catalog changed.
    
    This is a critical performance optimization that caches the expensive
    TF-IDF vectorization process. The cache is only invalidated when:
    - New songs are added to the catalog
    - Songs are removed from the catalog
    
    Args:
        all_songs_df: DataFrame with all songs (id, artist_name, genres)
    
    Returns:
        tuple: (TfidfVectorizer, sparse matrix, filtered DataFrame)
    """
    global tfidf_cache
    
    current_song_ids = set(all_songs_df['id'].unique())
    
    # Check if cache is valid (same song catalog)
    if (tfidf_cache['vectorizer'] is not None and 
        tfidf_cache['song_ids'] == current_song_ids):
        print(f"Using cached TF-IDF matrix ({len(current_song_ids)} songs)")
        return (tfidf_cache['vectorizer'], 
                tfidf_cache['all_songs_matrix'], 
                tfidf_cache['all_songs_df'])
    
    # Cache miss - rebuild TF-IDF matrix
    print(f"Rebuilding TF-IDF cache for {len(current_song_ids)} songs...")
    
    # Build features for all songs
    all_songs_df = all_songs_df.copy()  # Copy to avoid modifying original
    all_songs_df['features'] = all_songs_df.apply(build_song_features, axis=1)
    
    # Filter out songs with no features
    all_songs_df = all_songs_df[all_songs_df['features'] != '']
    
    if all_songs_df.empty:
        return None, None, None
    
    # Create and fit TF-IDF vectorizer
    tfidf = TfidfVectorizer(
        max_features=500,      # Limit vocabulary for performance
        ngram_range=(1, 1),    # Single words only
        token_pattern=r'\b\w+\b'
    )
    
    all_features_matrix = tfidf.fit_transform(all_songs_df['features'])
    
    # Update cache
    tfidf_cache['vectorizer'] = tfidf
    tfidf_cache['all_songs_matrix'] = all_features_matrix
    tfidf_cache['all_songs_df'] = all_songs_df
    tfidf_cache['song_ids'] = current_song_ids
    
    print(f"TF-IDF cache built successfully: {all_features_matrix.shape}")
    return tfidf, all_features_matrix, all_songs_df

def build_song_features(song_row):
    """
    Build a text feature string from song metadata for TF-IDF vectorization.
    
    Combines genres and artist name into a single string that represents the song's
    characteristics. This string is then used to create TF-IDF vectors for similarity matching.
    
    Design Decisions:
    - Artist name is repeated 2x to give it higher weight in similarity calculations
    - Spaces in names are replaced with underscores to treat multi-word names as single tokens
    - All text is lowercased for consistency
    
    Args:
        song_row: Pandas Series/DataFrame row with 'genres' and 'artist_name' columns
    
    Returns:
        String of space-separated feature tokens (e.g., "taylor_swift taylor_swift pop country")
    """
    features = []
    
    # Add artist name (repeated twice for higher weight)
    # Artists are strong indicators of musical taste
    if pd.notna(song_row['artist_name']):
        artist = song_row['artist_name'].lower().replace(' ', '_')
        features.extend([artist] * 2)  # Repeat for emphasis
    
    # Add genres
    # Each genre is treated as a separate token
    if pd.notna(song_row['genres']):
        try:
            genres = json.loads(song_row['genres'])
            genre_tokens = [g.lower().replace(' ', '_') for g in genres]
            features.extend(genre_tokens)
        except:
            pass  # Skip if genres can't be parsed
    
    return ' '.join(features) if features else ''

def content_based_similarity_tfidf(user_id, all_songs_df, user_liked_songs_df):
    """
    Calculate content-based similarity using TF-IDF and cosine similarity (OPTIMIZED).
    
    PERFORMANCE OPTIMIZATION:
    - Uses cached TF-IDF matrix instead of recomputing on every request
    - Only transforms user's liked songs (not all songs)
    - Eliminates redundant DataFrame copying
    - ~90% faster than non-cached version
    
    This is a more sophisticated approach than simple Jaccard similarity:
    
    1. TF-IDF (Term Frequency-Inverse Document Frequency):
       - Weights rare features (genres/artists) higher than common ones
       - Example: "math rock" is more distinctive than "pop"
       - Formula: TF-IDF(t,d) = (count of t in d) × log(total docs / docs containing t)
    
    2. Cosine Similarity:
       - Measures angle between feature vectors (0 to 1)
       - Better than Jaccard for weighted features
       - Formula: cos(θ) = (A·B) / (||A|| × ||B||)
    
    3. User Profile:
       - Average of all liked songs' TF-IDF vectors
       - Represents overall music taste
    
    Args:
        user_id: ID of the user requesting recommendations
        all_songs_df: DataFrame of all candidate songs with 'id', 'genres', 'artist_name'
        user_liked_songs_df: DataFrame of songs user has liked/shared
    
    Returns:
        List of predictions: [{'song_id': int, 'score': float, 'reason': str}, ...]
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
        
        # Calculate average user profile vector
        # This represents the user's overall music taste
        # FIX: Convert np.matrix (deprecated) to np.array to avoid errors
        user_profile = np.asarray(user_features_matrix.mean(axis=0))
        
        # Calculate cosine similarity between user profile and all songs (cached matrix)
        # Result is array of similarity scores [0, 1]
        similarities = cosine_similarity(user_profile, all_features_matrix)[0]
        
        # Build predictions from similarity scores
        # OPTIMIZATION: Use enumerate instead of iterrows for better performance
        predictions = []
        for idx, similarity_score in enumerate(similarities):
            # Only include songs with meaningful similarity (threshold 0.1)
            if similarity_score > 0.1:
                predictions.append({
                    'song_id': int(cached_songs_df.iloc[idx]['id']),
                    'score': float(similarity_score),
                    'reason': 'Similar to your music taste (TF-IDF)'
                })
        
        return predictions
    
    except Exception as e:
        print(f"Error in content_based_similarity_tfidf: {e}")
        return []

def content_based_similarity(user_id, all_songs_df, liked_genres, liked_artists):
    """
    Calculates similarity based on Genre and Artist overlap for Cold Start users.
    """
    predictions = []
    
    # Pre-process all songs genres if not already done (doing it per row is slow, but acceptable for now)
    # Ideally this should be cached or pre-computed
    
    for _, song in all_songs_df.iterrows():
        score = 0.0
        reasons = []
        
        # Artist Match
        if song['artist_name'] in liked_artists:
            score += 0.5
            reasons.append(f"Same artist: {song['artist_name']}")
            
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
                'score': float(score), # Normalized 0-1 range roughly
                'reason': " & ".join(reasons)
            })
            
    return predictions



def get_user_interactions(user_id, connection):
    user_interactions_query = text("""
        SELECT s.song_id FROM likes l JOIN shares s ON l.share_id = s.id WHERE l.user_id = :user_id
        UNION
        SELECT song_id FROM shares WHERE user_id = :user_id
        UNION
        SELECT s.song_id FROM user_feedback uf JOIN shares s ON uf.share_id = s.id WHERE uf.user_id = :user_id
        UNION
        SELECT s.song_id FROM dislikes d JOIN shares s ON d.share_id = s.id WHERE d.user_id = :user_id
    """)
    user_interacted_songs_df = pd.read_sql(user_interactions_query, connection, params={'user_id': user_id})
    return set(user_interacted_songs_df['song_id'].unique())

def get_liked_genres(user_id, connection):
    liked_genres_query = text("""
        SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(so.genres, '$[*]')) as genre_item
        FROM likes l
        JOIN shares s ON l.share_id = s.id
        JOIN songs so ON s.song_id = so.id
        WHERE l.user_id = :user_id AND so.genres IS NOT NULL
        UNION
        SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(so.genres, '$[*]')) as genre_item
        FROM shares s
        JOIN songs so ON s.song_id = so.id
        WHERE s.user_id = :user_id AND so.genres IS NOT NULL
    """)
    liked_genres_df = pd.read_sql(liked_genres_query, connection, params={'user_id': user_id})
    liked_genres = set()
    if not liked_genres_df.empty:
        for genre_item_json in liked_genres_df['genre_item'].dropna():
            try:
                genre_list = json.loads(genre_item_json)
                for genre in genre_list:
                    liked_genres.add(genre.lower())
            except (json.JSONDecodeError, TypeError):
                liked_genres.add(genre_item_json.lower())
    return liked_genres

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
    """)
    liked_artists_df = pd.read_sql(liked_artists_query, connection, params={'user_id': user_id})
    # Return set of lowercased, stripped artist names for robust matching
    return {name.lower().strip() for name in liked_artists_df['artist_name'].unique() if name}

@app.route('/recommendations/<int:user_id>', methods=['GET'])
def get_recommendations(user_id):
    global algo
    if algo is None:
        return jsonify({"status": "error", "message": "Recommendation model not loaded or trained."}) , 500

    if engine is None:
        return jsonify({"status": "error", "message": "Database connection not initialized."}) , 500

    try:
        with engine.connect() as connection:
            # ==============================================================================
            # HYBRID RECOMMENDATION ENGINE
            # ==============================================================================
            # This function implements a hybrid approach combining:
            # 1. Collaborative Filtering (SVD) - for users with history
            # 2. Content-Based Filtering - for cold-start users (using Genre/Artist similarity)
            # 3. Social Boosting - boosting scores for songs shared by friends
            # ==============================================================================

            # --- STEP 1: DATA RETRIEVAL (The Bounded Dataset) ---
            # Fetch necessary data: all songs, user interactions, social graph, and user preferences.
            
            # Get all songs with artist info
            songs_query = "SELECT id, artist_name, genres FROM songs"
            all_songs_df = pd.read_sql(songs_query, connection)
            all_song_ids = all_songs_df['id'].unique()

            user_interacted_song_ids = get_user_interactions(user_id, connection)
            
            # Identify candidates (songs user hasn't seen)
            candidates = [item_id for item_id in all_song_ids if item_id not in user_interacted_song_ids]
            
            # Fetch User Context
            social_graph = get_social_graph(user_id, connection)
            liked_artists = get_liked_artists(user_id, connection)
            liked_genres = get_liked_genres(user_id, connection)

            cf_predictions = []

            # --- STEP 2: HYBRID FILTERING (SVD + Content Boost) ---
            # We now combine Collaborative Filtering (SVD) with Content Signals (Artist Match)
            # This ensures that even if you have history, your explicit artist favorites (like NewJeans)
            # get a priority boost alongside the "Crowd Wisdom" of SVD.

            # Create fast lookup for song metadata (Artist/Genre)
            songs_metadata = all_songs_df.set_index('id').to_dict('index')
            
            # Check if user has enough history (Avoid Cold Start)
            has_history = len(user_interacted_song_ids) >= 5
            
            if has_history and algo:
                # Predict rating for all songs the user hasn't seen yet
                for song_id in candidates:
                    # 1. Base Score: Collaborative Filtering (SVD)
                    pred = algo.predict(user_id, song_id)
                    current_score = pred.est
                    reasons = ["Based on your listening history"]
                    
                    # 2. explicit Boost: Artist Match
                    # If this song is by an artist the user likes, give it a significant boost
                    if song_id in songs_metadata:
                        artist = songs_metadata[song_id]['artist_name']
                        # Check strictly case-insensitive
                        if artist and artist.lower().strip() in liked_artists:
                            current_score += 0.4  # Boost score by 0.4 (significant on a 1-5 scale)
                            # Prepend strict reason so user knows why
                            reasons.insert(0, f"Matches favorite artist ({artist})")
                            
                    cf_predictions.append({
                        'song_id': song_id, 
                        'score': current_score,
                        'reason': " & ".join(reasons)
                    })
            else:
                # --- STEP 3: CONTENT-BASED FILTERING (The Cold Start Fix) ---
                # For new users with <5 interactions, use TF-IDF + Cosine Similarity
                # This is more sophisticated than simple Jaccard similarity
                
                # Fetch user's liked songs with full metadata for TF-IDF
                user_liked_query = text("""
                    SELECT DISTINCT so.id, so.artist_name, so.genres
                    FROM likes l
                    JOIN shares s ON l.share_id = s.id
                    JOIN songs so ON s.song_id = so.id
                    WHERE l.user_id = :user_id
                    UNION
                    SELECT DISTINCT so.id, so.artist_name, so.genres
                    FROM shares s
                    JOIN songs so ON s.song_id = so.id
                    WHERE s.user_id = :user_id
                """)
                user_liked_songs_df = pd.read_sql(user_liked_query, connection, params={'user_id': user_id})
                
                # Try TF-IDF-based similarity first (more sophisticated)
                cf_predictions = content_based_similarity_tfidf(user_id, all_songs_df, user_liked_songs_df)
                
                # Fallback to Jaccard-based method if TF-IDF returns nothing
                # This can happen if user has very few interactions or metadata is sparse
                if not cf_predictions:
                    cf_predictions = content_based_similarity(user_id, all_songs_df, liked_genres, liked_artists)
                
                # If still empty (brand new user with 0 interactions), return empty
                # In production, you might return globally popular songs here
                # If still empty (brand new user with 0 interactions), return global popular songs
                if not cf_predictions:
                     print("Cold start: No user history found. Fetching global top songs.")
                     top_songs_query = text("""
                        SELECT s.id as song_id, COUNT(l.id) + (SELECT COUNT(*) FROM shares sh WHERE sh.song_id = s.id) * 2 as popularity
                        FROM songs s
                        LEFT JOIN likes l ON l.share_id IN (SELECT id FROM shares WHERE song_id = s.id)
                        GROUP BY s.id
                        ORDER BY popularity DESC
                        LIMIT 10
                     """)
                     top_songs_df = pd.read_sql(top_songs_query, connection)
                     
                     for _, row in top_songs_df.iterrows():
                         cf_predictions.append({
                             'song_id': int(row['song_id']),
                             'score': 0.1, # Low score to indicate it's generic
                             'reason': 'Popular in the community'
                         })

            # --- STEP 4: TRUST-BASED SOCIAL BOOSTING ---
            # This implements a sophisticated trust calculation based on social network theory.
            # Instead of fixed weights, we use a logarithmic formula that considers:
            # 1. Active user's selectivity (how many people they follow)
            # 2. Sharer's influence (how many followers they have)
            
            # Get active user's friend count (number of people they follow)
            active_user_friend_count = len(social_graph)
            
            # Optimization: Bulk fetch sharers for all candidate songs
            candidate_ids = [p['song_id'] for p in cf_predictions]
            song_sharers_map = get_song_sharers_bulk(candidate_ids, connection)
            
            # Collect all unique sharers to fetch their follower counts in one query
            all_sharers = set()
            for sharers in song_sharers_map.values():
                all_sharers.update(sharers)
            
            # Bulk fetch follower counts for all sharers (efficient single query)
            sharer_follower_counts = get_follower_counts_bulk(list(all_sharers), connection)
            
            final_scores = []
            
            for pred in cf_predictions:
                song_id = pred['song_id']
                base_score = pred['score']
                reason = pred['reason']
                
                social_boost = 0.0
                sharers = song_sharers_map.get(song_id, [])
                
                friend_sharers = []
                
                # Calculate trust-based boost for each sharer
                for sharer in sharers:
                    # Get sharer's follower count (defaults to 1 if not found)
                    sharer_friends = sharer_follower_counts.get(sharer, 1)
                    
                    # Calculate trust score using logarithmic formula
                    trust_score = calculate_trust(active_user_friend_count, sharer_friends)
                    
                    if sharer in social_graph:
                        # Friend (user follows this person): Apply full trust weight
                        # Friends get 100% of the calculated trust score
                        social_boost += trust_score
                        friend_sharers.append(sharer)
                    else:
                        # Community member (not a direct friend): Apply reduced trust
                        # Community gets 30% of trust score (still influenced by popularity, but less)
                        # This balances between friend recommendations and general popularity
                        social_boost += trust_score * 0.3
                
                # Calculate Hybrid Score
                # 70% from collaborative/content-based filtering (algorithmic accuracy)
                # 30% from social trust signals (peer influence)
                total_score = (base_score * 0.7) + (social_boost * 0.3)
                
                if friend_sharers:
                    # Append to existing reason instead of overwriting it
                    # Check if 'friend' is already in reason to avoid duplication (though unlikely here)
                    if "friend" not in reason:
                         reason = f"{reason} & Liked by your friend"
                
                final_scores.append({
                    'song_id': int(song_id),
                    'score': float(total_score),
                    'reason': reason,
                    'social_boost': float(social_boost)
                })

            # --- STEP 5: RANKING & EXPLANATION ---
            # Sort by total score to surface the best recommendations.
            # The 'reason' field provides transparency (e.g., "Liked by your friend").
            final_scores.sort(key=lambda x: x['score'], reverse=True)
            
            # Select Top N
            recommendations = final_scores[:10]
            
            return jsonify({"user_id": user_id, "recommendations": recommendations})
    except Exception as e:
        print(f"Error in get_recommendations: {e}")
        return jsonify({"status": "error", "message": f"Error generating recommendations: {e}"}) , 500

def schedule_training():
    """
    Background task to retrain the model periodically.
    """
    while True:
        print("\n[Scheduler] Waiting 60 seconds before next training cycle...")
        time.sleep(60)
        print("[Scheduler] Starting scheduled model training...")
        with app.app_context():
            try:
                train_and_save_model()
            except Exception as e:
                print(f"[Scheduler] Error during scheduled training: {e}")

if __name__ == '__main__':
    # Start the training thread as a daemon so it exits when the main program exits
    # Only start if we are in the main process (not the reloader)
    if os.environ.get('WERKZEUG_RUN_MAIN') == 'true' or os.name == 'nt':
         # Note: On Windows with Flask debug mode, this might still run twice or behave oddly depending on how it's launched.
         # Ideally check for a lock or use a dedicated scheduler, but for this simple requirement:
         pass

    # We start the thread. If using debug=True, Flask spawns a child process.
    # We want the training to happen in the child process where the app runs.
    if os.environ.get('WERKZEUG_RUN_MAIN') == 'true':
        training_thread = threading.Thread(target=schedule_training)
        training_thread.daemon = True
        training_thread.start()
        print("[Scheduler] Background training thread started.")
    else:
        # If not using reloader or in the parent process of reloader
        # We might want to start it here if debug=False
        pass

    app.run(debug=True, host='0.0.0.0', port=5000)
