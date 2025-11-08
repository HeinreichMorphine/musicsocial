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

def post_process_predictions(predictions, all_songs_df, liked_artists, liked_genres):
    artist_boost_factor = 0.25 # Boost score by 25%
    genre_boost_factor = 0.20 # Boost score by 20%
    combined_boost_factor = 0.5 # Boost score by 50% for combined artist and genre match
    songs_map = all_songs_df.set_index('id')

    for pred in predictions:
        song_id = pred['song_id']
        artist_name = songs_map.loc[song_id, 'artist_name']
        song_genres_json = songs_map.loc[song_id, 'genres']

        # Start with a generic reason
        pred['reason'] = 'Recommended for you'

        # Determine if there are matching genres for the current song
        has_matching_genres = False
        matching_genres_for_reason = set()
        if isinstance(song_genres_json, str) and liked_genres:
            try:
                current_genres = {g.lower() for g in json.loads(song_genres_json)}
                matching_genres_for_reason = current_genres.intersection(liked_genres)
                if matching_genres_for_reason:
                    has_matching_genres = True
            except (json.JSONDecodeError, TypeError):
                pass

        # --- Positive Boosting Logic ---
        # Combined Artist and Genre Boost
        if any(liked_artist.lower().strip() in artist_name.lower().strip() for liked_artist in liked_artists) and has_matching_genres:
            pred['score'] *= (1 + combined_boost_factor)
            display_artist_name = artist_name.split(',')[0].strip()
            pred['reason'] = f'Because you enjoy {display_artist_name}'
        # Artist-based boost
        elif any(liked_artist.lower().strip() in artist_name.lower().strip() for liked_artist in liked_artists):
            pred['score'] *= (1 + artist_boost_factor)
            display_artist_name = artist_name.split(',')[0].strip()
            pred['reason'] = f'Because you enjoy {display_artist_name}'
        # Genre-based boost
        elif has_matching_genres:
            pred['score'] *= (1 + genre_boost_factor)
            pred['reason'] = f'Because you enjoy {", ".join(list(matching_genres_for_reason)).title()} genres'

        # If no specific reason is found, it's likely due to taste neighbors
        if pred['reason'] == 'Recommended for you' and pred['score'] > 1.0: # A score > 1 suggests a boost from the model itself
            pred['reason'] = f'Popular with users who have similar tastes to you, and you might like artists such as {artist_name}'

    return predictions

def predict_scores(user_id, items_to_predict):
    global algo
    predictions = []
    for item_id in items_to_predict:
        prediction = algo.predict(uid=user_id, iid=item_id, r_ui=None)
        predictions.append({'song_id': int(item_id), 'score': prediction.est})
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
            # Get all songs with artist info
            songs_query = "SELECT id, artist_name, genres FROM songs"
            all_songs_df = pd.read_sql(songs_query, connection)
            all_song_ids = all_songs_df['id'].unique()

            user_interacted_song_ids = get_user_interactions(user_id, connection)

            liked_artists = get_liked_artists(user_id, connection)

            # Get genres from songs the user has liked or shared
            liked_genres = get_liked_genres(user_id, connection)

            items_to_predict = [item_id for item_id in all_song_ids if item_id not in user_interacted_song_ids]

            predictions = predict_scores(user_id, items_to_predict)

            predictions = post_process_predictions(predictions, all_songs_df, liked_artists, liked_genres)

            predictions.sort(key=lambda x: x['score'], reverse=True)

            top_n_recommendations = predictions[:10]

            return jsonify({"user_id": user_id, "recommendations": top_n_recommendations})
    except Exception as e:
        print(f"Error in get_recommendations: {e}")
        return jsonify({"status": "error", "message": f"Error generating recommendations: {e}"}) , 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
