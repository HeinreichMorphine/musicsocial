from flask import Flask, jsonify, request
import os
from dotenv import load_dotenv
from sqlalchemy import create_engine, text
import pandas as pd
import json
from surprise import Dataset, Reader, SVD
from surprise.model_selection import train_test_split
import joblib

# Global variable for the trained model
algo = None
engine = None

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
        engine = create_engine(db_uri)
        # Test the connection
        with engine.connect() as connection:
            print("Successfully connected to the database using SQLAlchemy")
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
            interactions_df = pd.concat([likes_df, feedback_df, dislikes_df, shares_df, following_likes_df, following_shares_df], ignore_index=True)
            if interactions_df.empty:
                print("--- No interaction data found in any table. ---")
                return pd.DataFrame()

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
    return set(liked_artists_df['artist_name'].unique())

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

            # --- STEP 2: COLLABORATIVE FILTERING (The Pattern Recognizer) ---
            # Use Scikit-Surprise library (SVD) to predict ratings based on user history.
            
            # Check if user has enough history (Avoid Cold Start)
            # We consider "enough history" as having interacted with at least 5 songs
            has_history = len(user_interacted_song_ids) >= 5
            
            if has_history and algo:
                # Predict rating for all songs the user hasn't seen yet
                for song_id in candidates:
                    pred = algo.predict(user_id, song_id)
                    cf_predictions.append({
                        'song_id': song_id, 
                        'score': pred.est,
                        'reason': "Based on your listening history"
                    })
            else:
                # --- STEP 3: CONTENT-BASED FILTERING (The Cold Start Fix) ---
                # If user is new, find songs similar to their few onboarding selections
                # using Genre and Artist similarity (Jaccard Index).
                cf_predictions = content_based_similarity(user_id, all_songs_df, liked_genres, liked_artists)
                # If still empty (brand new user with 0 interactions), return popular songs? 
                # For now, let's assume they have at least 1 interaction or we return empty.
                if not cf_predictions:
                     # Fallback to random or popular if absolutely no info (not implemented here for brevity, 
                     # but in production we'd return global top 10)
                     pass

            # --- STEP 4: SOCIAL BOOSTING (The "Peer-Based" Component) ---
            # This addresses the "Trust Deficit" gap by boosting songs shared by friends.
            
            # Get list of people who shared/liked the candidate songs
            # Optimization: Bulk fetch sharers for all candidate songs
            candidate_ids = [p['song_id'] for p in cf_predictions]
            song_sharers_map = get_song_sharers_bulk(candidate_ids, connection)
            
            final_scores = []
            
            for pred in cf_predictions:
                song_id = pred['song_id']
                base_score = pred['score']
                reason = pred['reason']
                
                social_boost = 0.0
                sharers = song_sharers_map.get(song_id, [])
                
                friend_sharers = []
                
                # Check if any sharers are in the active user's "Following" list
                for sharer in sharers:
                    if sharer in social_graph:
                        # Apply weight (Social signals improve prediction)
                        social_boost += 1.5 # Arbitrary weight for direct friends
                        friend_sharers.append(sharer) # We would look up name here if we had it loaded
                    else:
                        # Smaller boost for general community popularity
                        social_boost += 0.1 
                
                # Calculate Hybrid Score
                # We value Social Context + Algorithmic Accuracy
                # Normalize base_score (SVD is 1-5 usually, but our training data was -1 to 1.5. 
                # Let's assume SVD output is roughly within that range or slightly wider)
                
                total_score = (base_score * 0.7) + (social_boost * 0.3)
                
                if friend_sharers:
                    reason = "Liked by your friend" # Simplified for now, ideally "Liked by Alex"
                
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

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
