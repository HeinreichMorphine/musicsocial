from flask import Flask, jsonify, request
import os
from dotenv import load_dotenv
import mysql.connector
from mysql.connector import Error
import pandas as pd
from surprise import Dataset, Reader, SVD
from surprise.model_selection import train_test_split
import joblib

# Global variable for the trained model
algo = None

# Load environment variables from .env file
load_dotenv()

app = Flask(__name__)

# Database configuration
DB_HOST = os.getenv('DB_HOST')
DB_DATABASE = os.getenv('DB_DATABASE')
DB_USERNAME = os.getenv('DB_USERNAME')
DB_PASSWORD = os.getenv('DB_PASSWORD')

def get_db_connection():
    connection = None
    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            database=DB_DATABASE,
            user=DB_USERNAME,
            password=DB_PASSWORD
        )
        if connection.is_connected():
            print("Successfully connected to the database")
        return connection
    except Error as e:
        print(f"Error connecting to MySQL database: {e}")
        return None

def fetch_data_from_db():
    connection = get_db_connection()
    if connection is None:
        return pd.DataFrame()

    try:
        # Positive interactions from likes
        likes_query = "SELECT user_id, share_id FROM likes"
        likes_df = pd.read_sql(likes_query, connection)
        likes_df['interaction'] = 1

        # Negative interactions from feedback
        feedback_query = "SELECT user_id, share_id FROM user_feedback WHERE feedback_type = 'not_interested'"
        feedback_df = pd.read_sql(feedback_query, connection)
        feedback_df['interaction'] = -1

        # Combine all interactions
        interactions_df = pd.concat([likes_df, feedback_df])
        interactions_df = interactions_df.rename(columns={'share_id': 'item_id'})
        interactions_df = interactions_df[['user_id', 'item_id', 'interaction']]

        # Remove duplicates, keeping the most recent feedback (if any)
        # In this setup, likes are removed when feedback is given, but this handles any edge cases.
        interactions_df = interactions_df.drop_duplicates(subset=['user_id', 'item_id'], keep='last')

        print(f"Fetched {len(interactions_df)} user-item interactions.")
        return interactions_df
    except Error as e:
        print(f"Error fetching data: {e}")
        return pd.DataFrame()
    finally:
        if connection and connection.is_connected():
            connection.close()

MODEL_PATH = 'surprise_model.pkl'

def train_and_save_model():
    global algo
    print("Fetching data for model training...")
    interactions_df = fetch_data_from_db()

    if interactions_df.empty:
        print("No data to train the model. Skipping training.")
        return

    reader = Reader(rating_scale=(-1, 1)) # Adjusted rating scale
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
        print(f"Loading model from {MODEL_PATH}...")
        algo = joblib.load(MODEL_PATH)
        print("Model loaded successfully.")
    else:
        print("No model found. Training a new one.")
        train_and_save_model()

# Load model on startup
with app.app_context():
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
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/test_db_connection')
def test_db_connection():
    connection = get_db_connection()
    if connection:
        connection.close()
        return jsonify({"status": "success", "message": "Database connection successful!"})
    else:
        return jsonify({"status": "error", "message": "Failed to connect to database."}), 500

@app.route('/recommendations/<int:user_id>', methods=['GET'])
def get_recommendations(user_id):
    global algo
    if algo is None:
        return jsonify({"status": "error", "message": "Recommendation model not loaded or trained."}), 500

    connection = get_db_connection()
    if connection is None:
        return jsonify({"status": "error", "message": "Failed to connect to database for item fetching."}), 500

    try:
        # Get all shares with artist info
        shares_query = "SELECT id, artist_name FROM shares"
        all_shares_df = pd.read_sql(shares_query, connection)
        all_share_ids = all_shares_df['id'].unique()

        # Get items the user has already interacted with
        user_interactions_query = f"""
            SELECT share_id FROM likes WHERE user_id = {user_id}
            UNION
            SELECT id as share_id FROM shares WHERE user_id = {user_id}
            UNION
            SELECT share_id FROM user_feedback WHERE user_id = {user_id}
        """
        user_interacted_shares_df = pd.read_sql(user_interactions_query, connection)
        user_interacted_share_ids = set(user_interacted_shares_df['share_id'].unique())

        # Get artists the user has liked
        liked_artists_query = f"""
            SELECT DISTINCT s.artist_name
            FROM likes l
            JOIN shares s ON l.share_id = s.id
            WHERE l.user_id = {user_id}
        """
        liked_artists_df = pd.read_sql(liked_artists_query, connection)
        liked_artists = set(liked_artists_df['artist_name'].unique())

        items_to_predict = [item_id for item_id in all_share_ids if item_id not in user_interacted_share_ids]

        predictions = []
        for item_id in items_to_predict:
            prediction = algo.predict(uid=user_id, iid=item_id, r_ui=None)
            predictions.append({'share_id': int(item_id), 'score': prediction.est})

        # Post-processing: Boost scores and add reasons
        artist_boost_factor = 0.15 # Boost score by 15%
        shares_map = all_shares_df.set_index('id')

        for pred in predictions:
            share_id = pred['share_id']
            artist_name = shares_map.loc[share_id, 'artist_name']
            # Default reason
            pred['reason'] = 'Based on your taste'
            if artist_name in liked_artists:
                pred['score'] *= (1 + artist_boost_factor)
                pred['reason'] = f'Because you like {artist_name}'

        predictions.sort(key=lambda x: x['score'], reverse=True)

        top_n_recommendations = predictions[:10]

        return jsonify({"user_id": user_id, "recommendations": top_n_recommendations})
    except Error as e:
        print(f"Error in get_recommendations: {e}")
        return jsonify({"status": "error", "message": f"Error generating recommendations: {e}"}), 500
    finally:
        if connection and connection.is_connected():
            connection.close()

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)