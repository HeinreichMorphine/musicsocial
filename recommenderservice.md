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
4.  The Python service queries the database for user interaction data, predicts the best recommendations, and returns a list of `share_id`s.
5.  The Laravel application then displays these shares to the user.

## 3. Installation and Setup

To set up and run the recommender service, follow these steps:

1.  **Navigate to the service directory:**
    ```bash
    cd C:\laragon\www\musicsocial\recommender_service
    ```

2.  **Create and activate a Python virtual environment:**
    ```bash
    python -m venv venv
    .\venv\Scripts\activate
    ```

3.  **Install dependencies:**
    ```bash
    pip install -r requirements.txt
    ```

4.  **Set up environment variables:**
    Create a `.env` file in the `recommender_service` directory. It should contain the same database credentials as the main Laravel application's `.env` file.
    ```
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

## 4. How the Recommendation Algorithm Works (Simplified)

Our system suggests new music by understanding your taste through your interactions and those of similar users.

### 1. Gathering Your Music Footprint

We collect data on how you interact with music, assigning a "weight" to each action:

*   **Strong Positive (`1.5`):** Your own shared songs.
*   **Positive (`1.0` - `1.2`):** Your likes, and songs liked by or shared by users you follow.
*   **Negative (`-1.0`):** Your dislikes, and songs you've marked as "not interested."

The highest weight for any interaction with a song is prioritized.

### 2. Learning Your Taste (SVD Model)

All this data trains a smart algorithm called **SVD**. It learns your unique taste patterns and the characteristics of each song, creating a "taste profile" for you and a "feature profile" for every song.

### 3. Predicting New Songs

When you seek recommendations, your "taste profile" is compared to songs you haven't heard. The system generates a "raw score" predicting how much it thinks you'd like each new song.

### 4. Fine-Tuning and Explaining

Raw scores are adjusted with extra rules, and a reason is provided:

*   **Generic:** "Recommended for you."
*   **Negative:** "Less relevant: From followed user, but not matching your taste" (if from a followed user but not aligned with your taste or disliked).
*   **Positive:**
    *   "Because someone you follow shared it."
    *   "Because you enjoy [Artist Name] and similar genres."
    *   "Because you enjoy [Artist Name]."
    *   "Because you enjoy [Genre]."
*   **Community:** "Popular with users who have similar tastes."

### 5. Your Personalized Playlist

Finally, songs are sorted by their refined scores, and the top recommendations are presented to you on the Discovery page, each with a clear explanation of why it was suggested.

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