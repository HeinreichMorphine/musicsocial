# MusicSocial: A Music-Centric Social Network

## Project Overview

MusicSocial is a web application that allows users to share and discover music. It's a social network where users can connect with each other, share their favorite tracks from Spotify, and engage in discussions. The application also features a recommendation system that suggests new music to users based on their tastes.

## Architecture

The application is built with the following technologies:

*   **Backend**: Laravel (PHP framework)
*   **Frontend**: Blade templates, Tailwind CSS, and Alpine.js
*   **Database**: MySQL
*   **Recommendation System**: A separate Python service built with Flask and the Surprise library.
*   **APIs**: Spotify API and YouTube API

The application follows a monolithic architecture for the main web application, with a separate microservice for the recommendation system. The Laravel backend handles user authentication, data persistence, and serves the frontend. The recommendation service is responsible for generating personalized music recommendations for each user.

## Key Features

*   **User Authentication**: Users can register, log in, and manage their profiles. Authentication is handled by Laravel Breeze.
*   **Music Sharing**: Users can share music from Spotify by providing a Spotify track ID. The application fetches track details, including audio features, from the Spotify API.
*   **Text Posts**: Users can create text-based posts to share their thoughts.
*   **Social Feed**: The main dashboard displays a feed of shares from the users that the current user follows.
*   **User Profiles**: Each user has a public profile page that displays their shares and other information.
*   **Comments**: Users can comment on shares, and comments can be nested to create discussion threads.
*   **Likes**: Users can like shares to show their appreciation.
*   **Follows**: Users can follow each other to build their social network.
*   **Music Discovery**: The application provides a "Discovery" page where users can find new music based on recommendations from the recommendation system.
*   **Search**: Users can search for other users on the platform.

## Database Schema

The database schema is designed to support the application's features. The main tables are:

*   `users`: Stores user information, including name, email, and password.
*   `shares`: Stores the content shared by users, which can be a music track or a text post. It includes details about the shared music, such as the Spotify track ID, track name, artist name, and album art.
*   `comments`: Stores comments made by users on shares. It supports nested comments.
*   `likes`: A pivot table that stores the "like" relationships between users and shares.
*   `followers`: A pivot table that stores the "follow" relationships between users.
*   `user_feedback`: Stores user feedback on recommendations, such as "not interested". This data is used to improve the recommendation model.

## Recommendation System

The recommendation system is a key feature of MusicSocial. It's a separate Python service that uses the Surprise library to generate personalized music recommendations.

*   **Model**: The recommendation model is based on Singular Value Decomposition (SVD), a collaborative filtering algorithm.
*   **Training Data**: The model is trained on user interactions with shares, including likes and "not interested" feedback.
*   **API**: The recommendation service exposes an API that the Laravel application can call to get recommendations for a specific user.
*   **Retraining**: The model can be retrained by sending a POST request to the `/retrain` endpoint of the recommendation service.

## Dependencies

### Backend (PHP - Composer)

*   `laravel/framework`: The core Laravel framework.
*   `laravel/breeze`: A starter kit for authentication.
*   `laravel/tinker`: An interactive REPL for Laravel.
*   `guzzlehttp/guzzle`: An HTTP client for making requests to external APIs (Spotify, YouTube, and the recommendation service).

### Frontend (JavaScript - npm)

*   `@tailwindcss/forms`: A plugin for Tailwind CSS that provides basic styles for form elements.
*   `alpinejs`: A rugged, minimal framework for composing JavaScript behavior in your markup.
*   `autoprefixer`: A PostCSS plugin to parse CSS and add vendor prefixes to CSS rules.
*   `axios`: A promise-based HTTP client for the browser and Node.js.
*   `laravel-vite-plugin`: A Vite plugin for Laravel.
*   `postcss`: A tool for transforming CSS with JavaScript.
*   `tailwindcss`: A utility-first CSS framework.
*   `vite`: A build tool that aims to provide a faster and leaner development experience for modern web projects.

# Netcentric Elements: Enhancing Interactivity with YouTube & Spotify APIs

This document outlines the core netcentric components of the MusicSocial platform, detailing how the integration of the YouTube and Spotify APIs enables a highly interactive and engaging user experience.

---

## 1. Core Principle: A Connected Music Ecosystem

At its heart, MusicSocial is a netcentric application that thrives on the seamless exchange of data and functionality with external services. By integrating with major music platforms like YouTube and Spotify, we transform our application from a standalone social network into a dynamic hub for music discovery and interaction. This approach not only enriches our feature set but also grounds our platform in the broader digital music landscape that users already inhabit.

---

## 2. Key APIs for High Interactivity

Our primary netcentric elements are the APIs provided by Spotify and YouTube. These services are crucial for fetching music data, enabling playback, and facilitating user interactions that bridge our platform with users' external music accounts.

### Spotify Web API
- **Role:** The Spotify API is used to search for tracks, fetch detailed metadata (artist, album, artwork), and retrieve audio features. This allows users to share specific songs and provides the rich data needed for our recommendation system.
- **Interactivity:** It forms the backbone of our music sharing feature, allowing users to search and embed Spotify tracks directly within their posts.

### YouTube Data API
- **Role:** The YouTube API enables searching for music videos and retrieving relevant information (title, channel, thumbnail). This is essential for users who prefer to discover and share music in a video format.
- **Interactivity:** Similar to the Spotify integration, it allows users to find and share YouTube videos, making the platform accessible to a wider range of content.

---

## 3. Feasibility Study: "Add to Playlist" Feature

To further enhance interactivity, we are exploring the implementation of features that allow users to directly manage their external music libraries from within MusicSocial. The most prominent example is an "Add to Playlist" feature.

### Concept
A user could click a button on a shared track within MusicSocial to add that song directly to one of their personal playlists on Spotify or YouTube.

### Feasibility & Implementation Steps

**1. Authorization (OAuth 2.0):**
- **Requirement:** This is the most critical and complex step. To modify a user's playlist, we must obtain their explicit permission. This requires implementing the OAuth 2.0 authorization flow for both Spotify and **Google (for YouTube)**.
- **Process:**
    - The user would need to connect their Spotify/YouTube account to their MusicSocial profile.
    - This would redirect them to an official consent screen from the respective service, asking them to grant our application specific permissions (e.g., `playlist-modify-public`, `playlist-modify-private` for Spotify; `https://www.googleapis.com/auth/youtube` for YouTube).
    - Upon approval, the service would provide us with an access token and a refresh token. These tokens must be securely stored and associated with the user's account.
- **Feasibility:** **High.** OAuth 2.0 is a standard, well-documented protocol. Libraries for Laravel (like Socialite) can simplify this process significantly.

**2. API Endpoints:**
- **Spotify:** The Spotify Web API has a dedicated endpoint to [Add Items to a Playlist](https://developer.spotify.com/documentation/web-api/reference/add-tracks-to-playlist). We would need the user's access token, the playlist ID, and the track URI.
- **YouTube:** The YouTube Data API provides an `playlistItems.insert` endpoint to [add a video to a playlist](https://developers.google.com/youtube/v3/docs/playlistItems/insert). This requires the user's access token, the playlist ID, and the video ID.
- **Feasibility:** **High.** The required API endpoints are well-documented and straightforward to use once authorization is handled.

**3. User Interface (UI/UX):**
- **Implementation:** We would need to add a new button or menu option on each shared track card. Upon clicking, a modal could appear, allowing the user to select which of their playlists they want to add the song to.
- **Challenges:** We would need to fetch and display a list of the user's existing playlists, which requires an additional API call. We also need to handle cases where a user has not yet connected their account, prompting them to do so.
- **Feasibility:** **Medium.** While not technically difficult, designing an intuitive and seamless user experience requires careful planning.

### Conclusion on Feasibility
The "Add to Playlist" feature is **highly feasible** from a technical standpoint. The primary challenge lies in the correct and secure implementation of the OAuth 2.0 flow and in designing a user-friendly interface. The value this feature would add in terms of user engagement and platform "stickiness" is substantial, as it directly integrates MusicSocial into the user's daily music consumption habits.

# Recommendation System: Design Philosophy & Strategy

This document outlines the core principles of our recommendation system, focusing on how its unique design solves key problems inherent in modern music discovery platforms.

---

## 1. Current Features & Algorithm

Our recommendation system is built on a **Collaborative Filtering (CF)** model using the **Surprise** library. The core algorithm is **Singular Value Decomposition (SVD)**.

### Key Features:

- **Platform-Wide Discovery:** The model is trained on interactions from all users on the platform, allowing it to find "taste-neighbors"—users with similar listening patterns—even if they don't follow each other.

- **Positive Feedback:** User actions like **liking** a share are treated as a strong positive signal (a rating of `+1`).

- **Negative Feedback ("Not Interested"):** We have implemented a crucial "Not Interested" feature. When a user marks a share as not interesting, it is recorded as a strong negative signal (a rating of `-1`). This does not hide the content but significantly lowers the recommendation weight for similar songs in the future.

- **Artist Weighting:** The model boosts the recommendation score for songs by artists the user has previously liked.

- **Recommendation Reasons:** The API provides a reason for each recommendation (e.g., "Because you like [Artist]", "Based on your taste").

- **Recommendation Pool:** The recommendation API provides the **top 10 recommendations** for each user.

- **Metadata-Ready:** The application now collects and stores rich metadata for each track, including Spotify `audio_features` (danceability, energy, valence) and YouTube `tags`. While the current SVD model does not use this data directly in its core algorithm, it is ready for future implementation of more advanced hybrid models.

---

## 2. System Management & Maintenance

### Running the Services (Development Mode)

To run the full application, you need to have three separate processes running in three separate terminals:

1.  **Laravel Web Server:**
    ```bash
    php artisan serve
    ```

2.  **Recommendation Service (Python):
    ```bash
    # Make sure the service is not already running in the background
    C:\laragon\www\musicsocial\recommender_service\venv\Scripts\python.exe C:\laragon\www\musicsocial\recommender_service\app.py
    ```

3.  **Laravel Task Scheduler (for automated retraining):
    ```bash
    php artisan schedule:work
    ```

### Automated Model Retraining

The system is configured to automatically retrain the recommendation model every hour. This is handled by the Laravel Task Scheduler, which calls the `/retrain` endpoint on the Python service.

### Manual Commands

These Artisan commands are available for managing the recommendation system's data.

-   **Trigger a Manual Retrain:**
    This command sends a request to the Python service to immediately retrain the model with the latest data.
    ```bash
    php artisan app:retrain-recommender
    ```

-   **Backfill Metadata for Existing Shares:**
    This command will iterate through all existing shares and fetch any missing Spotify audio features or YouTube tags.
    ```bash
    php artisan app:backfill-share-metadata
    ```

-   **Clear All Share Data:**
    This command permanently deletes all shares, likes, and comments from the database. **Use with caution.**
    ```bash
    php artisan app:clear-shares-data
    ```

---

## 3. Solving the "Filter Bubble"

### The Problem
Current recommendation systems often trap users in a "filter bubble," leading to a monotonous and repetitive listening experience. By analyzing a user's entire listening history, they tend to reinforce existing tastes rather than encouraging genuine discovery, limiting exposure to new and diverse sounds.

### Our Solution: Bounded Collaborative Filtering
Our "Discovery" feed is powered by a CF algorithm that **only analyzes interaction patterns from within our platform**. This crucial distinction prevents the system from just reinforcing a user's external listening habits. Instead, it surfaces music that has been shared and validated by their peers *within our community*, creating an ecosystem where discovery is driven by trusted, internal sources, not by pre-existing biases.

---

## 4. Solving "Popularity Bias"

### The Problem
Mainstream recommendation engines have a "significant popularity bias" that creates a visibility barrier for new, independent, or niche musicians. These systems favor artists who are already popular, making it difficult for emerging talent to be discovered.

### Our Solution: Community-Driven Discovery
Our dataset is **"strictly bounded,"** a defining feature that means the algorithm **will only recommend songs already shared by platform users**. A song cannot enter the recommendation pool unless a member of our community shares it first. This design directly enables the **"grassroots, word-of-mouth promotion"** that is central to our mission. Tracks gain visibility through genuine **community validation rather than pre-existing popularity metrics**, leveling the playing field for all artists.

---

## 5. Data Requirements for Training

The model is trained on a dataset derived from the following sources:

-   **User-Item Interactions:**
    -   `likes`: `user_id`, `share_id` (strong positive signal: `+1`)
    -   `user_feedback`: `user_id`, `share_id`, `feedback_type` (strong negative signal: `-1` for 'not_interested')

-   **Social Graph:**
    -   `followers`: `follower_id`, `following_id` (to understand influence and taste clusters). While this data provides valuable context, the core CF algorithm is not restricted to it and operates on the entire user-item interaction dataset.

-   **Item Metadata (Collected for future use):**
    -   **Spotify:** `track_id`, `artist`, `album`, `genre`, `audio_features` (danceability, energy, valence).
    -   **YouTube:** `video_id`, `title`, `channel_title`, `tags`.

---

## 6. Future Enhancements

-   **Hybrid Model:** Incorporating the collected item metadata (audio features, tags, genres) into a more advanced hybrid recommendation model to further improve suggestion quality.

# MusicSocial UI Design

This document outlines the UI design for the MusicSocial application, including the grid-based layout, sharing functionality, and page-specific designs.

## 1. General Layout & Grid System

The application will use a responsive grid-based layout to ensure a consistent and intuitive user experience across different devices. The layout will be built using Tailwind CSS's grid system.

### Main Container

*   A main container will wrap the content of each page, with a maximum width of `1280px` and centered on the page.
*   The container will have horizontal padding to provide space on smaller screens.

### Grid Structure

*   The main content area will be a two-column grid.
*   **Left Column**: The main content of the page, such as the social feed or user profile.
*   **Right Column**: A sidebar containing supplementary information, such as the user's profile summary, recommended users, or other relevant content.
*   On smaller screens (e.g., mobile), the two columns will stack vertically, with the main content appearing first.

## 2. Sharing Functionality

The sharing functionality is a core feature of the application. It allows users to share music from Spotify or YouTube, or to create a text-based post.

### Sharing Form

*   A sharing form will be prominently displayed at the top of the main feed.
*   The form will have a text area for the user to write their post.
*   Below the text area, there will be buttons to trigger the music sharing functionality (e.g., "Share Music").

```html
<div class="bg-white p-4 shadow rounded-lg mb-6">
    <form action="/shares" method="POST">
        @csrf
        <textarea name="content" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="What's on your mind?"></textarea>
        <div class="mt-2 flex justify-between items-center">
            <div>
                <button type="button" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Share Music</button>
                <!-- Potentially other media sharing buttons -->
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Post</button>
        </div>
    </form>
</div>
```

### Sharing Modal

*   When the user clicks the "Share Music" button, a modal will appear.
*   The modal will have two tabs: "Spotify" and "YouTube".
*   **Spotify Tab**: A search bar will allow the user to search for a track on Spotify. The results will be displayed in a list, and the user can select a track to share.
*   **YouTube Tab**: A search bar will allow the user to search for a video on YouTube. The results will be displayed in a list, and the user can select a video to share.
*   Once the user selects a track or video, the modal will close, and the selected item will be attached to the post.

## 3. Page-Specific Designs

### Dashboard (Feed)

*   The dashboard will display a chronological feed of shares from the users that the current user follows.
*   Each share will be displayed as a card, with the user's avatar, name, and the content of the share.
*   If the share is a music track, the card will display the track's artwork, name, and artist.
*   Each share will have buttons for liking, commenting, and other interactions.

```html
<div class="bg-white p-4 shadow rounded-lg mb-6">
    <div class="flex items-center mb-4">
        <img class="w-10 h-10 rounded-full mr-4" src="/path/to/avatar.jpg" alt="User Avatar">
        <div>
            <p class="font-semibold">User Name</p>
            <p class="text-gray-500 text-sm">2 hours ago</p>
        </div>
    </div>
    <div class="mb-4">
        <p>This is a text post from the user.</p>
        <!-- Or if it's a music share -->
        <div class="flex items-center mt-4">
            <img class="w-16 h-16 rounded-md mr-4" src="/path/to/album-art.jpg" alt="Album Art">
            <div>
                <p class="font-semibold">Track Name</p>
                <p class="text-gray-600">Artist Name</p>
            </div>
        </div>
    </div>
    <div class="flex justify-around text-gray-600">
        <button class="flex items-center"><i class="far fa-heart mr-1"></i> Like</button>
        <button class="flex items-center"><i class="far fa-comment mr-1"></i> Comment</button>
        <button class="flex items-center"><i class="fas fa-share mr-1"></i> Share</button>
    </div>
</div>
```

### User Profile

*   The user profile page will display the user's avatar, name, bio, and other information.
*   Below the user's information, there will be a feed of the user's shares.
*   The page will also display the user's followers and following lists.

```html
<div class="bg-white p-6 shadow rounded-lg">
    <div class="flex items-center">
        <img class="w-24 h-24 rounded-full mr-6" src="/path/to/avatar.jpg" alt="User Avatar">
        <div>
            <h1 class="text-2xl font-bold">User Name</h1>
            <p class="text-gray-600">This is the user's bio. It can be a short description of their musical tastes.</p>
            <div class="mt-4 flex space-x-4">
                <div>
                    <p class="font-semibold">123</p>
                    <p class="text-gray-500">Followers</p>
                </div>
                <div>
                    <p class="font-semibold">456</p>
                    <p class="text-gray-500">Following</p>
                </div>
            </div>
        </div>
    </div>
</div>
```

### Discovery Page

*   The discovery page will display a grid of recommended music tracks.
*   Each track will be displayed as a card with its artwork, name, and artist.
*   The user can click on a track to view more details or to listen to a preview.

```html
<div class="bg-white shadow rounded-lg overflow-hidden">
    <img class="w-full h-48 object-cover" src="/path/to/album-art.jpg" alt="Album Art">
    <div class="p-4">
        <h3 class="font-semibold text-lg">Track Name</h3>
        <p class="text-gray-600">Artist Name</p>
        <div class="mt-4">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Listen</button>
        </div>
    </div>
</div>
```

### Settings Page

*   The settings page will allow the user to update their profile information, change their password, and manage their account settings.
*   The page will be divided into sections for different settings.

```html
<div class="bg-white p-6 shadow rounded-lg">
    <h2 class="text-xl font-bold mb-4">Profile Settings</h2>
    <form action="/settings/profile" method="POST">
        @csrf
        @method('PATCH')
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-semibold mb-2">Name</label>
            <input type="text" name="name" id="name" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('name', $user->name) }}">
        </div>
        <div class="mb-4">
            <label for="bio" class="block text-gray-700 font-semibold mb-2">Bio</label>
            <textarea name="bio" id="bio" class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" rows="3">{{ old('bio', $user->bio) }}</textarea>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
    </form>
</div>
```
