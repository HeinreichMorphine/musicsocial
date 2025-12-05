# Recommender Service Documentation

## 1. Running the Service

To start the Recommender Service, follow these steps to open and run `app.py`:

1.  **Open your terminal** (Command Prompt, PowerShell, or Git Bash).
2.  **Navigate to the service directory:**
    ```bash
    cd C:\laragon\www\musicsocial-main\recommender_service
    ```
3.  **Activate the virtual environment:**
    *   **Command Prompt / PowerShell:**
        ```powershell
        .\venv\Scripts\activate
        ```
    *   **Git Bash:**
        ```bash
        source venv/Scripts/activate
        ```
4.  **Run the Flask application:**
    ```bash
    python app.py
    ```
    *Alternatively, if you have issues with activation, you can run it directly:*
    ```bash
    .\venv\Scripts\python.exe app.py
    ```

The service will start running at `http://127.0.0.1:5000`.

---

## 2. API Endpoints

The Flask application exposes the following endpoints for interaction:

-   `GET /`: **Health Check**. Confirms that the service is running.
-   `POST /retrain`: **Trigger Retraining**. Initiates the model retraining process manually.
-   `GET /recommendations/<user_id>`: **Get Recommendations**. Returns a list of recommended songs for a specific user.
-   `GET /test_db_connection`: **Test Database**. Verifies that the service can connect to the MySQL database.

---

## 3. How It Works

Our hybrid recommender system combines multiple sophisticated algorithms to provide personalized music recommendations.

### 1. Gathering Your Music Footprint
We collect data on how you interact with music, assigning a "weight" to each action:
*   **Your Own Shares (`1.5`):** Strongest indicator of preference.
*   **Likes from Followed Users (`1.2`):** High social influence.
*   **Your Likes (`1.0`):** Standard positive signal.
*   **Shares from Followed Users (`0.8`):** Moderate social signal.
*   **Dislikes (`-1.0`):** Negative feedback.
*   **"Not Interested" Feedback (`-1.0`):** Explicit negative signal.

### 2. Collaborative Filtering (For Established Users)
For users with ≥5 interactions, we use **Singular Value Decomposition (SVD)**:
*   Learns your unique taste profile by analyzing patterns across all users
*   Predicts ratings for songs you haven't seen
*   Parameters: 20 epochs, learning rate 0.005, regularization 0.02

### 3. Content-Based Filtering (For New Users)
For users with <5 interactions (cold-start), we use **TF-IDF + Cosine Similarity**:
*   **TF-IDF**: Weights rare genres/artists higher than common ones (e.g., "math rock" > "pop")
*   **Cosine Similarity**: Measures similarity between your taste profile and candidate songs
*   **Fallback**: Simple Jaccard similarity if metadata is sparse

### 4. Trust-Based Social Boosting
We amplify recommendations using a dynamic trust formula based on social network influence:
*   **Formula**: `t(uₐ, uᵢ) = 1/(1 + log(F(uₐ))) · log(F(uᵢ))`
*   **Friends**: Get 100% of calculated trust score
*   **Community**: Get 30% of trust score
*   Considers both your selectivity (how many you follow) and sharer's influence (their followers)

### 5. Hybrid Scoring
Final recommendations combine:
*   **70%** Collaborative/Content-based score (algorithmic accuracy)
*   **30%** Social trust boost (peer influence)

### 6. Ranking & Explanation
Top 10 songs are returned with explanations:
*   "Based on your listening history" (SVD)
*   "Similar to your music taste (TF-IDF)" (Content-based)
*   "Liked by your friend" (Social boost)

---

## 4. Installation

If you are setting this up for the first time, follow these steps:

1.  **Navigate to the service directory:**
    ```bash
    cd C:\laragon\www\musicsocial-main\recommender_service
    ```
2.  **Create a virtual environment:**
    ```bash
    python -m venv venv
    ```
3.  **Activate the environment** (see Section 1).
4.  **Install dependencies:**
    ```bash
    pip install -r requirements.txt
    pip install scikit-learn  # For TF-IDF and cosine similarity
    ```
5.  **Configure Environment:**
    Ensure a `.env` file exists in `recommender_service/` with your database credentials:
    ```
    DB_HOST=127.0.0.1
    DB_DATABASE=musicsocial
    DB_USERNAME=root
    DB_PASSWORD=your_password
    ```
6.  **Initial Model Training:**
    ```bash
    python train.py
    ```
    This creates the initial `surprise_model.pkl` file.

---

## 5. Management and Interaction

The recommender service can also be managed through Laravel's Artisan console commands.

### Automated Model Retraining
The system is configured to automatically retrain the model every hour via the Laravel Task Scheduler.
To run the scheduler:
```bash
php artisan schedule:work
```

### Manual Artisan Commands
-   **Trigger Retrain:** `php artisan app:retrain-recommender`
-   **Backfill Metadata:** `php artisan app:backfill-share-metadata`
-   **Clear Data:** `php artisan app:clear-shares-data`