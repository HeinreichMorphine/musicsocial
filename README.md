# Musicsocial

Musicsocial is a web application that allows users to share and discover music from Spotify and YouTube. It's built with Laravel and includes a recommendation system to help users find new music based on their tastes.

## Features

*   Share music from Spotify and YouTube
*   Like and comment on shares
*   Follow other users
*   Search for users
*   User profiles with followers and following lists
*   Recommendation system for music discovery

## Tech Stack

*   Laravel
*   PHP
*   MySQL
*   Tailwind CSS
*   JavaScript
*   Python (for the recommendation system)

## Getting Started

### Prerequisites

*   PHP >= 8.1
*   Composer
*   Node.js & npm
*   A web server (e.g., Nginx, Apache)
*   A database (e.g., MySQL, PostgreSQL)

### Installation

1.  Clone the repository:
    ```bash
    git clone https://github.com/HeinreichMorphine/musicsocial.git
    ```
2.  Navigate to the project directory:
    ```bash
    cd musicsocial
    ```
3.  Install PHP dependencies:
    ```bash
    composer install
    ```
4.  Install JavaScript dependencies:
    ```bash
    npm install
    ```
5.  Create a copy of the `.env.example` file and name it `.env`:
    ```bash
    cp .env.example .env
    ```
6.  Generate an application key:
    ```bash
    php artisan key:generate
    ```
7.  Configure your database credentials in the `.env` file.
8.  Run the database migrations:
    ```bash
    php artisan migrate
    ```
9.  Start the development server:
    ```bash
    php artisan serve
    ```
10. In a separate terminal, compile the assets:
    ```bash
    npm run dev
    ```

Now you can access the application at `http://localhost:8000`.

## Recommendation System

The recommendation system is a separate Python service that provides music recommendations to users. It's located in the `recommender_service` directory. To run the recommendation system, you'll need to install the Python dependencies:

```bash
pip install -r recommender_service/requirements.txt
```

Then, you can run the recommendation service:

```bash
python recommender_service/app.py
```

## Contributing

Contributions are welcome! Please feel free to submit a pull request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Acknowledgments

- **Beets**: Genre normalization data from the [Beets](https://github.com/beetbox/beets) project (specifically `lastgenre` plugin).