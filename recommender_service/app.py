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

# Global Song Cache (Metadata)
song_cache = {
    'df': None,
    'last_update': 0
}

def get_cached_songs(connection):
    """
    Returns the dataframe of all songs, using a cache to avoid repetitive DB queries.
    Refreshes cache if it's empty or older than 1 hour.
    """
    global song_cache
    current_time = time.time()
    CACHE_DURATION = 3600  # 1 hour
    
    # If cache is valid, return it
    if song_cache['df'] is not None and (current_time - song_cache['last_update'] < CACHE_DURATION):
        # Optimization: Return copy to prevent accidental modification affecting other requests
        return song_cache['df'] # Pandas copy is not needed if we are just reading, but safer if modifying. 
                                # In this app, we filter it later, which creates a copy anyway.
    
    print("Refreshing Song Cache...")
    query = "SELECT id, track_name, artist_name, genres FROM songs"
    df = pd.read_sql(query, connection)
    
    song_cache['df'] = df
    song_cache['last_update'] = current_time
    print(f"Song Cache Refreshed: {len(df)} songs.")
    return df


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

    print("\n--- Starting Data Fetch (Weighted Interaction Logic) ---")
    try:
        with engine.connect() as connection:
            # 1. Likes (1 Point)
            likes_query = "SELECT l.user_id, s.song_id, 2.0 as score FROM likes l JOIN shares s ON l.share_id = s.id"
            likes_df = pd.read_sql(likes_query, connection)
            print(f"1. Likes (2pt): Found {len(likes_df)} records.")

            # 2. Shares (3 Points)
            shares_query = "SELECT user_id, song_id, 3.0 as score FROM shares"
            shares_df = pd.read_sql(shares_query, connection)
            print(f"2. Shares (3pts): Found {len(shares_df)} records.")
            
            # 3. Comments (2 Points)
            # Comments are linked to shares, so we join to get the song_id
            comments_query = "SELECT c.user_id, s.song_id, 1.0 as score FROM comments c JOIN shares s ON c.share_id = s.id"
            comments_df = pd.read_sql(comments_query, connection)
            print(f"3. Comments (1pts): Found {len(comments_df)} records.")

            # --- AGGREGATION & FORMULA APPLICATION ---
            # Combine all positive engagements
            engagement_df = pd.concat([likes_df, shares_df, comments_df], ignore_index=True)
            
            if not engagement_df.empty:
                # Sum scores per user-song pair (rui = Total Engagement Score)
                grouped_df = engagement_df.groupby(['user_id', 'song_id'])['score'].sum().reset_index()
                
                # Apply Logarithmic Formula: cui = 1 + alpha * log(1 + rui/epsilon)
                # Using alpha=1.0 and epsilon=1.0 as standard defaults
                # Formula: 1 + np.log(1 + grouped_df['score'])
                grouped_df['interaction'] = 1 + np.log(1 + grouped_df['score'].astype(float))
                
                print(f"Aggregated {len(engagement_df)} raw interactions into {len(grouped_df)} unique weighted scores.")
            else:
                grouped_df = pd.DataFrame(columns=['user_id', 'song_id', 'interaction'])

            # 4. Dislikes (Strong Negative -1.0)
            dislikes_query = "SELECT d.user_id, s.song_id, -1.0 as interaction FROM dislikes d JOIN shares s ON d.share_id = s.id"
            dislikes_df = pd.read_sql(dislikes_query, connection)
            print(f"4. Dislikes (-1.0): Found {len(dislikes_df)} records.")

            # 5. Song Interactions (Direct User-Song Actions)
            interactions_query = "SELECT user_id, song_id, type FROM song_interactions"
            direct_interactions_df = pd.read_sql(interactions_query, connection)
            
            if not direct_interactions_df.empty:
                # Map interaction types to scores
                # like -> 2.0, listen -> 1.0, dislike -> -1.0
                direct_interactions_df['score'] = direct_interactions_df['type'].map({
                    'like': 2.0,
                    'listen': 1.0,
                    'dislike': -1.0
                })
                
                # Separate positives and negatives
                positive_direct = direct_interactions_df[direct_interactions_df['score'] > 0][['user_id', 'song_id', 'score']]
                negative_direct = direct_interactions_df[direct_interactions_df['score'] < 0][['user_id', 'song_id', 'score']].rename(columns={'score': 'interaction'})
                
                print(f"5. Song Interactions: Found {len(positive_direct)} positive and {len(negative_direct)} negative direct actions.")
                
                # Append to main dataframes
                engagement_df = pd.concat([engagement_df, positive_direct], ignore_index=True)
                dislikes_df = pd.concat([dislikes_df, negative_direct], ignore_index=True)



            # Combine Weighted Personal Scores + Dislikes + Social Signals
            final_dfs = [grouped_df.rename(columns={'song_id': 'item_id'}), 
                         dislikes_df.rename(columns={'song_id': 'item_id'})]
            
            interactions_df = pd.concat([df for df in final_dfs if not df.empty], ignore_index=True)
            
            # Handle duplicates: Dislikes should override positive scores
            # Sort by interaction ascending logic is tricky here because -1 is small.
            # Strategy: Split positives and negatives.
            # Actually, SVD can handle multiple entries per user-item, but usually we want one.
            # If we simply drop duplicates keeping 'last' or 'first', we rely on sort order.
            # Let's trust that 'Dislike' (-1.0) and 'Weighted Score' (>1.0) are distinct signals.
            # Ideally, if I Dislike, I shouldn't get a positive score.
            # Let's drop duplicates by keeping the entries from Dislikes table if present?
            # Simplest for now: keep the HIGHEST absolute engagement? No.
            # Let's keep the one that appears LAST in current concat order? 
            # interaction_df = grouped + dislikes + following.
            # If I drop duplicates subset=['user_id', 'item_id'], keep='last' -> Followed Likes will win over Personal?
            # Or Dislikes (-1) will win over Personal?
            # Better: Group by user/item and take Mean? No.
            # Let's stick to simple drop duplicates for now to avoid complexity, 
            # assume Dislikes override previous positives if the user deleted the Like?
            # Actually, standard logic: keep the one with MAX weight is risky if Dislike is -1.
            # Let's keep duplicates for now, SVD will average them out or we can refine later.
            # But the 'Reader' duplicates check might complain.
            # Let's use `drop_duplicates(keep='first')` assuming `grouped_df` (Personal) is most important, 
            # BUT if Dislike exists... 
            # We will perform a clean drop: Prioritize Dislike (-1) > Personal (>1) > Social (1.2) ??
            # Actually, if I personally interacted (>1), I probably like it, unless I *subsequently* disliked it.
            # Let's assumes Personal Engagement is ground truth.
            interactions_df = interactions_df.drop_duplicates(subset=['user_id', 'item_id'], keep='first')

            print(f"\nTotal unique training records: {len(interactions_df)}")
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

    # Adjusted scale for new Weighted Formula
    # 1 + log(1 + ~20) is around 4.0. Max theoretical could be higher but 1-5 is standard.
    # We set scale (-1, 6) to be safe.
    reader = Reader(rating_scale=(-1, 6)) 
    data = Dataset.load_from_df(interactions_df[['user_id', 'item_id', 'interaction']], reader)

    trainset = data.build_full_trainset()

    print("Training SVD model...")
    algo = SVD(n_epochs=20, lr_all=0.005, reg_all=0.02, random_state=42)
    algo.fit(trainset)
    print("Model training complete.")

    joblib.dump(algo, MODEL_PATH)
    print(f"Model saved to {MODEL_PATH}")
    
    # Invalidate song cache to ensure next request fetches fresh metadata
    global song_cache
    song_cache['df'] = None
    print("Song cache invalidated after retraining.")


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
    
    # Calculate logarithmic components using the specific "Power Log" formula
    
    # 1. Numerator (Influence): ln(1 + |F_sharer|^0.7)
    #    The 0.7 exponent dampens the "superstar" effect before the log is taken.
    numerator = math.log(1.0 + (pow(sharer_friends, 0.7)))
    
    # 2. Denominator (Dilution): 1 + 0.5 * ln(1 + |F_active|)
    #    The 0.5 factor halves the penalty for following many people.
    denominator = 1.0 + (0.5 * math.log(1.0 + active_user_friends))
    
    trust = numerator / denominator
    
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
                song_id = int(cached_songs_df.iloc[idx]['id'])
                song_data = cached_songs_df.iloc[idx]
                
                # Build detailed reason based on what matched
                reason_parts = []
                artist_matched = False
                if pd.notna(song_data.get('artist_name')):
                    artist = song_data['artist_name']
                    # Check if this artist is in user's liked artists
                    user_artists = {row['artist_name'] for _, row in user_liked_songs_df.iterrows() if pd.notna(row.get('artist_name'))}
                    if artist in user_artists:
                        reason_parts.append(f"You enjoy {artist}")
                        artist_matched = True
                    else:
                        reason_parts.append(f"Similar to artists you like")
                
                # Add genre info only if it actually matches user's taste
                if pd.notna(song_data.get('genres')):
                    try:
                        genres = json.loads(song_data['genres'])
                        if genres:
                            # We need to know user's liked genres here to be accurate
                            # Since we don't pass liked_genres to this function, we can infer 
                            # or just be more generic if we can't verify.
                            # BETTER: Pass liked_genres or calculate intersection if possible.
                            # For now, let's just avoid saying "Matches your taste in X" unless we are sure.
                            # Actually, we can just say "Similar to songs you like" which is true because of TF-IDF.
                            pass 
                    except:
                        pass
                
                # If we have an exact artist match, explicit is better
                if artist_matched:
                     pass # Already added "You enjoy [Artist]"
                else:
                     # For generic TF-IDF matches, be more generic unless we know the specific feature
                     reason_parts.append("Similar to songs you shared")

                reason = " · ".join(reason_parts) if reason_parts else 'Matches your music taste'
                
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
                'score': float(score), # Normalized 0-1 range roughly
                'reason': " & ".join(reasons),
                'artist_matched': artist_matched
            })
            
    return predictions



def get_user_interactions(user_id, connection):
    user_interactions_query = text("""
        SELECT s.song_id FROM likes l JOIN shares s ON l.share_id = s.id WHERE l.user_id = :user_id
        UNION
        SELECT song_id FROM shares WHERE user_id = :user_id
        UNION
        SELECT s.song_id FROM dislikes d JOIN shares s ON d.share_id = s.id WHERE d.user_id = :user_id
        UNION
        SELECT song_id FROM song_interactions WHERE user_id = :user_id
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
            
            # Get all songs (OPTIMIZED: Use Cache)
            all_songs_df = get_cached_songs(connection)
            all_song_ids = all_songs_df['id'].unique()

            # Fetch User Dislikes explicitly to filter them out
            disliked_song_ids = []
            try:
                dislikes_q = text("SELECT DISTINCT s.song_id FROM dislikes d JOIN shares s ON d.share_id = s.id WHERE d.user_id = :uid")
                dislikes_res = connection.execute(dislikes_q, {'uid': user_id})
                disliked_song_ids = [row[0] for row in dislikes_res]
                print(f"User {user_id} disliked {len(disliked_song_ids)} songs.")
            except Exception as e:
                print(f"Error fetching dislikes: {e}")

            user_interacted_song_ids = get_user_interactions(user_id, connection)
            # Add dislikes to interactions so they are excluded from candidates
            user_interacted_song_ids = list(set(user_interacted_song_ids + disliked_song_ids))
            
            # Identify candidates (songs user hasn't seen)
            candidates = [item_id for item_id in all_song_ids if item_id not in user_interacted_song_ids]
            
            print(f"[DIAGRAM_TRACE] 1. Authentication & History Check: User {user_id} has {len(user_interacted_song_ids)} interactions.")
            
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
            
            if has_history:
                print(f"[DIAGRAM_TRACE] 2. Decision: Interactions >= 5 (YES). Entering Collaborative Filtering (SVD) Path.")
            else:
                print(f"[DIAGRAM_TRACE] 2. Decision: Interactions < 5 (NO). Entering Cold Start (Content-Based) Path.")

            if has_history and algo:
                # Predict rating for all songs the user hasn't seen yet
                for song_id in candidates:
                    # 1. Base Score: Collaborative Filtering (SVD)
                    pred = algo.predict(user_id, song_id)
                    current_score = pred.est
                    reasons = []
                    
                    # 2. Explicit Boost: Artist Match
                    # If this song is by an artist the user likes, give it a significant boost
                    artist_matched = False
                    if song_id in songs_metadata:
                        artist = songs_metadata[song_id]['artist_name']
                        # Check strictly case-insensitive
                        if artist and artist.lower().strip() in liked_artists:
                            current_score += 0.4
                            reasons.append(f"You've enjoyed {artist} before")
                            artist_matched = True
                    
                    # Add genre info if available and not artist matched
                    if not artist_matched and song_id in songs_metadata:
                        genres_data = songs_metadata[song_id].get('genres')
                        if genres_data:
                            try:
                                song_genres = set(g.lower() for g in json.loads(genres_data))
                                matching_genres = song_genres.intersection(liked_genres)
                                if matching_genres:
                                    genre_list = list(matching_genres)[:2]
                                    reasons.append(f"Matches your taste in {', '.join(genre_list)}")
                            except:
                                pass
                    
                    # Default reason if no specific match found
                    if not reasons:
                        reasons.append("Recommended based on your listening patterns")
                    
                    cf_predictions.append({
                        'song_id': song_id, 
                        'score': current_score,
                        'reason': " · ".join(reasons),
                        'artist_matched': artist_matched
                    })
            else:
                # --- STEP 3: COLD START (Most Popular Strategy) ---
                # User has < 5 interactions. Instead of guessing with content-based filtering,
                # we show the globally most popular songs to help them get started.
                
                print(f"[DIAGRAM_TRACE] 2b. Cold Start: Fetching Global Top 50 Songs.")
                
                # Weighted popularity query: Shares (3pts) + Likes (1pt)
                top_songs_query = text("""
                     SELECT s.id as song_id, 
                            (
                                (SELECT COUNT(*) FROM likes l WHERE l.share_id IN (SELECT id FROM shares WHERE song_id = s.id)) * 1 + 
                                (SELECT COUNT(*) FROM shares sh WHERE sh.song_id = s.id) * 3
                            ) as popularity
                     FROM songs s
                     ORDER BY popularity DESC
                     LIMIT 50
                """)
                
                try:
                    top_songs_df = pd.read_sql(top_songs_query, connection)
                    
                    for _, row in top_songs_df.iterrows():
                        s_id = int(row['song_id'])
                        score = float(row['popularity'])
                        
                        # Filter out what they have already seen/disliked
                        if s_id in user_interacted_song_ids:
                            continue
                            
                        # Normalize score for consistency with SVD scale (1-5)
                        # We use log to dampen huge popularity counts
                        display_score = min(5.0, 2.5 + np.log(1 + score))

                        cf_predictions.append({
                            'song_id': s_id,
                            'score': display_score,
                            'reason': 'Popular in the community'
                        })
                        
                except Exception as e:
                    print(f"Error serving popular songs: {e}")
                
                # --- POPULARITY FALLBACK (Fill if results are sparse) ---
                # If we have fewer than 10 recommendations, fill the rest with global popular songs.
                # This ensures even users with very little history get a full list.
                if len(cf_predictions) < 12:
                     print(f"Low result count ({len(cf_predictions)}). Filling with global top songs.")
                     needed = 12 - len(cf_predictions)
                     
                     # Simplified popularity query
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
                         
                         # Get existing IDs to avoid duplicates
                         existing_ids = {p['song_id'] for p in cf_predictions}
                         
                         filled_count = 0
                         for _, row in top_songs_df.iterrows():
                             s_id = int(row['song_id'])
                             
                             # Skip if already in predictions or already seen by user
                             if s_id in existing_ids or s_id in user_interacted_song_ids:
                                 continue

                             cf_predictions.append({
                                 'song_id': s_id,
                                 'score': 0.1, # Low score to indicate it's generic
                                 'reason': 'Popular in the community'
                             })
                             
                             filled_count += 1
                             if filled_count >= needed:
                                 break
                     except Exception as e:
                         print(f"Error fetching top songs: {e}")
                         # Fallback to random songs if query fails
                         fallback_query = text("SELECT id FROM songs ORDER BY RAND() LIMIT 12")
                         fallback_df = pd.read_sql(fallback_query, connection)
                         for _, row in fallback_df.iterrows():
                             if int(row['id']) not in user_interacted_song_ids:
                                cf_predictions.append({
                                    'song_id': int(row['id']),
                                    'score': 0.05,
                                    'reason': 'Popular in the community'
                                })

            # --- STEP 4: TRUST-BASED SOCIAL BOOSTING ---
            # This implements a sophisticated trust calculation based on social network theory.
            # Instead of fixed weights, we use a logarithmic formula that considers:
            # 1. Active user's selectivity (how many people they follow)
            # 2. Sharer's influence (how many followers they have)
            
            # Get active user's friend count (number of people they follow)
            active_user_friend_count = len(social_graph)
            
            print(f"[DIAGRAM_TRACE] 3. Social Graph Boosting: Analyzing social influence for {len(cf_predictions)} candidates.")
            
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
                trust_debug_info = {'influence': 0.0, 'dilution': 0.0} # Store last one for debug
                
                # Calculate trust-based boost for each sharer
                for sharer in sharers:
                    # Get sharer's follower count (defaults to 1 if not found)
                    sharer_friends = sharer_follower_counts.get(sharer, 1)
                    
                    # Calculate trust score using logarithmic formula
                    trust_score = calculate_trust(active_user_friend_count, sharer_friends)
                    
                    # Capture for debug log (just the last one is fine for the example)
                    # Re-calculate components locally to show in debug
                    t_num = math.log(1.0 + (pow(sharer_friends, 0.7)))
                    t_den = 1.0 + (0.5 * math.log(1.0 + max(1, active_user_friend_count)))
                    trust_debug_info = {'influence': t_num, 'dilution': t_den}
                    
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
                
                # Breakdown for UI
                svd_score = base_score
                context_boost_val = 0.0
                svd_score = base_score
                context_boost_val = 0.0
                artist_matched = pred.get('artist_matched', False)
                if artist_matched:
                    context_boost_val = 0.4
                 
                    svd_score = base_score - 0.4
                
                total_score = (base_score * 0.7) + (social_boost * 0.3)
                
                # Build social reasoning
                if friend_sharers:
                    friend_count = len(friend_sharers)
                    if friend_count == 1:
                        reason = f"{reason} · Shared by a friend you follow"
                    else:
                        reason = f"{reason} · {friend_count} friends shared this"
                elif len(sharers) > 0:
                    # Community signal (not friends but shared by others)
                    community_count = len(sharers)
                    if community_count >= 3:
                        reason = f"{reason} · Popular in your network ({community_count} shares)"
                
                final_scores.append({
                    'song_id': int(song_id),
                    'score': float(total_score),
                    'reason': reason,
                    'social_boost': float(social_boost),
                    'debug': {
                        'svd': float(svd_score),
                        'context': float(context_boost_val),
                        'weighted_base': float(base_score * 0.7),
                        'weighted_social': float(social_boost * 0.3),
                        'trust_components': trust_debug_info
                    }
                })

            # --- STEP 5: RANKING & EXPLANATION ---
            # Sort by total score to surface the best recommendations.
            # The 'reason' field provides transparency (e.g., "Liked by your friend").
            final_scores.sort(key=lambda x: x['score'], reverse=True)
            
            # Select Top N (Increased to 50 to allow client-side filtering of interactions)
            recommendations = final_scores[:50]
            
            # --- DEBUG LOGGING FOR ACCURACY TESTING ---
            print(f"\n--- Recommendation Score Breakdown for User {user_id} ---")
            print(f"{'Song ID':<10} | {'Base':<6} | {'Boost':<6} | {'Total':<6} | {'Reason'}")
            print("-" * 60)
            for rec in recommendations:
                print(f"{rec['song_id']:<10} | {rec['score'] - rec['social_boost']:.2f}   | {rec['social_boost']:.2f}   | {rec['score']:.2f}   | {rec['reason']}")
            print("-" * 60 + "\n")
            # ------------------------------------------
            
            if recommendations:
                top_rec = recommendations[0]
                tr_song_id = top_rec['song_id']
                tr_debug = top_rec['debug']
                
                # Get song name handling potential missing metadata
                song_name = "Unknown Song"
                if tr_song_id in songs_metadata:
                    song_name = songs_metadata[tr_song_id].get('track_name', 'Unknown Song')
                
                print(f"[DIAGRAM_TRACE] 3b. Master Formula (Top Recommendation: {song_name}):")
                print(f"   SVD Calc: Matrix Factorization ({tr_debug['svd']:.4f}) + Artist Boost ({tr_debug['context']})")
                print(f"   Trust Calc: Sum of Logarithmic Influence Scores ({tr_debug['trust_components']['influence']:.4f} / {tr_debug['trust_components']['dilution']:.4f})")
                print(f"   Total Score = (Base Score * 0.7) + (Social Trust * 0.3)")
                print(f"   {top_rec['score']:.4f}      = ({tr_debug['weighted_base']/.7:.4f} * 0.7) + ({tr_debug['weighted_social']/.3:.4f} * 0.3)")

            print(f"[DIAGRAM_TRACE] 4. Result: Returning {len(recommendations)} recommendations after sorting.")
            
            return jsonify({"user_id": user_id, "recommendations": recommendations})
    except Exception as e:
        print(f"Error in get_recommendations: {e}")
        return jsonify({"status": "error", "message": f"Error generating recommendations: {e}"}) , 500

def schedule_training():
    """
    Background task to retrain the model periodically.
    """
    while True:
        print("\n[Scheduler] Waiting 1 hour before next training cycle...")
        time.sleep(3600)
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
