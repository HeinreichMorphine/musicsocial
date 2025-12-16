# System Activity Diagram Description

This document describes the activity flow for the MusicSocial application, categorized by modules and actors. The system typically involves the **User**, **System (Web App)**, **Database**, and **External APIs** (Spotify, Google, Discogs, MusicBrainz, YouTube, Python Recommender).

## 1. Authentication Module
**Actors:** User, System, External Identity Provider (Spotify/Google), Database

1.  **User** initiates login by clicking "Login with Spotify", "Login with Google", or entering Email/Password.
2.  *External Login:* **System** redirects user to the Provider, then handles the callback to find/create the user.
3.  *Email Login:* **System** validates credentials directly against the **Database**.
4.  **Registration**: New email users trigger a **Send Verification Email** process.
5.  **System** logs the user in and redirects to Dashboard.

## 2. Music Sharing Module
**Actors:** User, System, Database, External APIs (Spotify, Discogs, MusicBrainz, YouTube).

1.  **User** submits a share (Music type) with a Spotify Link or Search term.
2.  **System** requests Track Details from **Spotify API**.
3.  **System** checks/creates Song record in **Database**.
4.  **System** enriches Song Metadata (Sequential Flow):
    *   **Spotify & Discogs Integration**: Fetches base genres from Spotify and detailed styles from Discogs.
    *   **Controller Level Enhancement**: Requests Artist Genres from **MusicBrainz API**.
    *   **YouTube Integration**: Extracts or searches for the **YouTube Link**.
5.  **System** updates Song in **Database** and creates a Share record.
6.  **System** returns the new Share card to **User**.

## 3. Feed & Interaction Module (Home)
**Actors:** User, System, Database.

1.  **User** views the Feed (Dashboard).
2.  **System** queries **Database** for Shares (Followed users + Own, Paginated).
    *   Eager loads: Song, User, Likes, Saved status.
3.  **User** interacts:
    *   **Like/Dislike**: Toggles status via AJAX.
    *   **Comment**: Submits comment text.
    *   **Save (Bookmark)**: Toggles saved status.
    *   **External Link**: Clicks to open Spotify/YouTube.

## 4. Discovery & Recommender Module
**Actors:** User, System, Python Recommender Service, Database.

1.  **User** views Discovery Page.
2.  **System** requests recommendations from **Python Recommender Service**:
    *   *Algorithm*: Hybrid (SVD for returning users, TF-IDF Content-Based for new users/cold start).
3.  **System** retrieves Song details for the recommended IDs.
4.  **System** renders the Main Recommendation Grid.
5.  **Sidebars**:
    *   **Who to Follow**: Taste Neighbor algorithm finds users with similar music taste.
    *   **Suggested Songs**: Sidebar song suggestions.

## 5. Profile Module
**Actors:** User, System, Database.

1.  **User** accesses Profile Page.
2.  **System** fetches Header Data (Badge, Follow Stats).
3.  **Tab Selection**:
    *   **Posts (Default)**: Displays user's share history.
    *   **Taste DNA**: Calculates Genre percentages and visualizes with a Radar Chart; finds "Taste Twins".
    *   **Saved**: (Owner Only) Displays bookmarked shares.
4.  **Interaction**: Edit Profile (Owner) or Follow/Unfollow (Visitor).

## 6. Admin Module
**Actors:** Admin User, System, Database, Python Service.

1.  **Login**: Access restricted to Admins via Guard; separate login flow.
2.  **Dashboard**: View system statistics (User/Share/Comment counts).
3.  **User Management**: Ban, Unban, or Delete users.
4.  **Moderation**: Review and Delete reported Shares or Comments.
5.  **Retrain/Test**: Interface to manually query the **Python Recommender** for a specific user to test algorithm output.
6.  **Profile**: Update Admin credentials.
