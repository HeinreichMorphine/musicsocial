# How MusicSocial Works

## Project Overview

MusicSocial is a web application designed for music enthusiasts to share and discover music from Spotify and YouTube. It provides a social platform where users can share tracks, follow other users, and engage with content through likes and comments. A key feature of the application is its recommendation system, which suggests new music to users based on their listening habits and preferences.

## Architecture

The application follows a monolithic architecture with a separate microservice for the recommendation engine.

*   **Backend (Laravel):** The core of the application is a monolithic backend built with the Laravel PHP framework. It handles user authentication, data management, and serves the frontend application.

*   **Frontend (Blade):** The user interface is constructed using Laravel's Blade templating engine, styled with Tailwind CSS. This setup allows for dynamic views that are rendered on the server side.

*   **Recommendation Service (Python):** A distinct microservice, developed in Python using the Flask framework, is responsible for generating music recommendations. This service communicates with the main Laravel application via an API, allowing for a separation of concerns and the use of Python's data science libraries for the recommendation algorithm.

*   **Database (MySQL):** A MySQL database is used to store all application data, including user information, shares, comments, and likes.

## Key Components

### Models

*   **User:** Represents a user in the application. It handles user authentication, profile information, and relationships with other entities like shares, comments, likes, and followers. Key relationships include:
    *   `shares()`: One-to-many relationship with the `Share` model, representing all music shares posted by the user.
    *   `comments()`: One-to-many relationship with the `Comment` model, representing all comments made by the user.
    *   `likes()`: Many-to-many relationship with the `Share` model, indicating which shares the user has liked.
    *   `dislikes()`: Many-to-many relationship with the `Share` model, indicating which shares the user has disliked.
    *   `following()`: Many-to-many relationship with the `User` model itself, representing the users that this user is following.
    *   `followers()`: Many-to-many relationship with the `User` model itself, representing the users who are following this user.

*   **Song:** Represents a song in the application, storing details such as track name, artist, album art, and external IDs from Spotify and YouTube. It has a one-to-many relationship with `Share` models, meaning a single song can be featured in multiple shares.
    *   `shares()`: One-to-many relationship with the `Share` model, representing all shares that feature this song.

*   **Share:** Represents a user's music share, linking a `User` to a `Song` and including a caption. This model also manages the lifecycle of associated `Song` records, deleting a song if it's no longer referenced by any shares. Key relationships include:
    *   `user()`: Many-to-one relationship with the `User` model, indicating the owner of the share.
    *   `song()`: Many-to-one relationship with the `Song` model, indicating the song being shared.
    *   `comments()`: One-to-many relationship with the `Comment` model, representing comments made on this share.
    *   `likes()`: Many-to-many relationship with the `User` model, representing users who liked this share.
    *   `dislikes()`: Many-to-many relationship with the `User` model, representing users who disliked this share.

*   **Comment:** Represents a comment made by a user on a music share. Comments can also be replies to other comments, forming a threaded discussion. Key relationships include:
    *   `user()`: Many-to-one relationship with the `User` model, indicating the author of the comment.
    *   `share()`: Many-to-one relationship with the `Share` model, indicating the share the comment belongs to.
    *   `parent()`: Many-to-many relationship with the `Comment` model itself, representing the parent comment if this is a reply.
    *   `replies()`: Many-to-many relationship with the `Comment` model itself, representing replies to this comment.

*   **CommentThread:** This pivot model manages the relationships between comments to form a threaded discussion structure. It links a `comment_id` to a `parent_id`, allowing for replies to comments. Key relationships include:
    *   `comment()`: Many-to-one relationship with the `Comment` model, representing the comment in the thread.
    *   `parent()`: Many-to-one relationship with the `Comment` model, representing the parent comment in the thread.

### Controllers

*   **Controller (Base):** The abstract base controller for all other controllers in the application. It provides a common foundation for shared functionalities like middleware, authorization, and request handling.

*   **CommentController:** Manages the creation, updating, and deletion of comments on music shares. It handles the logic for storing new comments, updating existing ones, and deleting them, ensuring that only authorized users can perform these actions.

*   **DiscoveryController:** Responsible for the discovery page, which provides personalized content recommendations. It fetches recommended songs from the `RecommendationService` and suggests users to follow based on shared musical tastes and popularity.

*   **FeedController:** Assembles the main dashboard feed for the authenticated user. It fetches a paginated list of shares from the user and the people they follow, recommended shares from the `RecommendationService`, and a list of users to suggest following.

*   **FollowController:** Handles the logic for following and unfollowing users. It provides a `toggle` method that allows the authenticated user to follow or unfollow another user.

*   **FollowerController:** Displays a user's followers and the users they are following. It provides `followers` and `following` methods that return paginated lists of users.

*   **LikeController:** Manages the liking and unliking of music shares. It provides a `toggle` method that allows the authenticated user to like or unlike a share, with safeguards to prevent liking one's own shares and to handle existing dislikes.