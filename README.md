# Reso (Resonance)

**Reso** is a netcentric social platform designed to connect people through their musical taste. Unlike traditional social networks that focus on status updates, Reso focuses on "Music Discovery via Social Connection." It combines a robust social layer (likes, comments, following) with an advanced recommendation engine that learns from user interactions to suggest new songs and potential friends.

## Features

### 1. Unified Music Sharing
*   **Share from Spotify & YouTube**: Users can post their favorite tracks directly to their feed.
*   **Smart Metadata**: The system automatically enhances shared songs with deep genre and style tags from MusicBrainz and Discogs.

### 2. Intelligent Discovery Engine
*   **Personalized Feed**: A "For You" style discovery page powered by a dedicated Python Microservice.
*   **Algorithmic Magic**: Uses **Collaborative Filtering (SVD)** to find patterns in user behavior and **Content-Based Filtering (TF-IDF)** to recommend songs based on acoustic traits and genres.

### 3. Deep Social Interactivity
*   **Taste Neighbors**: Find and follow users who have a mathematically similar taste profile to yours.
*   **Feedback Loop**: Every Like, Dislike, Bookmark, and Comment fine-tunes the recommendation algorithm in real-time.
*   **Threaded Discussions**: engage in deep conversations about music with nested comments and @mentions.

## Tech Stack

**The Core (Netcentric Web Server)**
*   **Laravel 10**: The robust PHP framework handling routing, authentication, and core business logic.
*   **MySQL**: Relational database storing user data, songs, and the social graph.

**The Brain (Machine Learning Microservice)**
*   **Python (Flask)**: A lightweight API service dedicated to heavy data processing.
*   **Scikit-Learn**: Powering the Matrix Factorization and Vector Space models.
*   **Pandas**: For efficient data manipulation of interaction computations.

**The External World (API Integrations)**
*   **Spotify Web API**: For real-time song search, metadata, and album art.
*   **YouTube Data API**: For fallback video streaming and tag extraction.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Acknowledgments

- **Beets**: Genre normalization data from the [Beets](https://github.com/beetbox/beets) project (specifically `lastgenre` plugin).
- **Discogs**: High-quality genre and style data for music classification.