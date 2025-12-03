# Recommender Service Documentation

## 1. Overview

This document outlines the architecture, setup, and operation of the Recommender Service for the MusicSocial application.

The service is a Python-based microservice that uses a collaborative filtering model to generate personalized music recommendations for users. It runs independently from the main Laravel application and communicates via a REST API.

## 2. System Architecture

The recommendation system is composed of two main parts:

-   **Laravel Application (MusicSocial):** The main web application that users interact with. It is responsible for sending requests for recommendations and triggering model retraining.
-   **Python Service (Flask API):** A lightweight Python server that exposes API endpoints to handle recommendation generation and model retraining.

The two systems share the same MySQL database, allowing the Python service to access user interaction data directly.

### Data Flow for Recommendations

1.  A user visits the "Discovery" page in the MusicSocial application.
2.  The `DiscoveryController` in Laravel calls the `RecommendationService`.
3.  The `RecommendationService` sends a GET request to the `/recommendations/<user_id>` endpoint on the Python service.
4.  The Python service queries the database for user interaction data, predicts the best recommendations, and returns a list of `song_id`s.
5.  The Laravel application then displays these shares to the user.

## 3. Installation and Setup

To set up and run the recommender service on Windows, follow these steps:

1.  **Navigate to the service directory:**
    ```bash
    cd C:\laragon\www\musicsocial-main\recommender_service
    ```

2.  **Create and activate a Python virtual environment:**
    
    *   **Option A: Using Command Prompt (cmd.exe) or PowerShell:**
        ```powershell
        python -m venv venv
        .\venv\Scripts\activate
        ```
        *(If successful, you will see `(venv)` at the start of your command line)*

    *   **Option B: Using Git Bash:**
        ```bash
        python -m venv venv
        source venv/Scripts/activate
        ```

3.  **Install dependencies:**
    ```bash
    pip install -r requirements.txt
    ```

4.  **Set up environment variables:**
    Create a `.env` file in the `recommender_service` directory. It should contain the same database credentials as the main Laravel application's `.env` file.
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=musicsocial
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  **Run the Flask application:**
    ```bash
    python app.py
    ```
    The service will now be running at `http://127.0.0.1:5000`.
### Restarting the Service

If you need to stop and restart the service later, you don't need to reinstall everything. Just activate the environment and run the app:

**Command Prompt / PowerShell:**
```powershell
cd C:\laragon\www\musicsocial-main\recommender_service
.\venv\Scripts\activate
python app.py
```

**Git Bash:**
```bash
cd C:\laragon\www\musicsocial-main/recommender_service
source venv/Scripts/activate
python app.py
```

## 4. How the Recommendation Algorithm Works (Simplified)

Our system suggests new music by understanding your taste through your interactions and those of similar users.

### 1. Gathering Your Music Footprint

We collect data on how you interact with music, assigning a "weight" to each action to understand your preferences:

*   **Your Own Shares (`1.5`):** Songs you share are the strongest indicator of your taste.
*   **Likes from Followed Users (`1.2`):** Songs liked by people you follow are highly influential.
*   **Your Likes (`1.0`):** A standard positive signal.
*   **Shares from Followed Users (`0.8`):** A weaker positive signal.
*   **Dislikes / "Not Interested" (`-1.0`):** Explicit negative feedback.

If you interact with the same song in multiple ways, we only consider the action with the highest weight.

### 2. Learning Your Taste (SVD Model)

This data trains a **Singular Value Decomposition (SVD)** algorithm. It's a collaborative filtering method that learns your unique taste profile by analyzing your interactions in the context of all other users' interactions.

### 3. Predicting New Songs

The trained model predicts a "raw score" for songs you haven't interacted with, estimating how much you might like them based on the learned patterns.

### 4. Fine-Tuning and Explaining

The raw scores are just the start. We then apply a post-processing layer to boost or reduce scores and provide a clear reason for each recommendation.

*   **Score Boosting:**
    *   **+50%:** If the song's artist and genre match your likes.
    *   **+25%:** If the artist matches your likes.
    *   **+20%:** If the genre matches your likes.
    *   **+10%:** If the song was shared by someone you follow (and you haven't disliked it).

*   **Score Reduction:**
    *   **-30%:** If a song is from a user you follow but doesn't align with your taste, we may still show it to you with a lower score to help you discover new things.

*   **Recommendation Reasons:**
    *   **Artist/Genre Match:** "Because you enjoy [Artist Name]" or "Because you enjoy [Genre]".
    *   **Followed User:** "Because someone you follow shared it."
    *   **Taste Neighbors:** "Popular with users who have similar tastes to you..."
    *   **Discovery:** "To broaden your horizon, here is a song from a user you follow."
    *   **Default:** "Recommended for you."

### 5. Your Personalized Playlist

Finally, the fine-tuned and sorted recommendations are presented to you on the Discovery page, each with a reason to explain why it was chosen for you.

## 5. API Endpoints

The Flask application exposes the following endpoints:

-   `GET /`: Confirms that the service is running.
-   `POST /retrain`: Initiates the model retraining process.
-   `GET /recommendations/<user_id>`: Returns recommendations for a given user.
-   `GET /test_db_connection`: Verifies the database connection.

## 6. Management and Interaction

The recommender service can be managed and interacted with through Laravel's Artisan console commands.

### Automated Model Retraining

The system is configured to automatically retrain the model every hour. This is handled by the Laravel Task Scheduler. To run the scheduler, use the following command:
```bash
php artisan schedule:work
```

### Manual Artisan Commands

-   **Trigger a Manual Retrain:**
    Immediately retrains the model with the latest data.
    ```bash
    php artisan app:retrain-recommender
    ```

-   **Backfill Metadata for Existing Shares:**
    Fetches any missing Spotify audio features or YouTube tags for existing shares.
    ```bash
    php artisan app:backfill-share-metadata
    ```

-   **Clear All Share Data:**
    Permanently deletes all shares, likes, and comments. **Use with caution.**
    ```bash
    php artisan app:clear-shares-data
    ```

## 7. Tech Stack

-   **Python:** Core programming language.
-   **Flask:** Micro web framework for the API.
-   **scikit-surprise:** Library for building recommender systems.
-   **Pandas:** Data manipulation and analysis.
-   **SQLAlchemy:** Database toolkit for connecting to MySQL.

## 8. File Structure

```
recommender_service/
├── app.py                # Main Flask application
├── requirements.txt      # Python dependencies
├── surprise_model.pkl    # Pre-trained recommendation model
└── venv/                 # Python virtual environment
```