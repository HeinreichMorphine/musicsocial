module.exports = {
    apps: [{
        name: "musicsocial-recommender",
        script: "recommender_service/app.py",
        interpreter: "recommender_service/venv/Scripts/python.exe",
        instances: 1,
        autorestart: true,
        watch: false,
        max_memory_restart: "1G",
        env: {
            NODE_ENV: "development",
            FLASK_ENV: "development"
        }
    }]
};
