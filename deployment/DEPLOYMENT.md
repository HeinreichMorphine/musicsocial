# DigitalOcean Droplet Deployment Guide for Reso (MusicSocial)

This guide provides a comprehensive, production-grade guide for deploying the Reso social music discovery platform to a fresh Ubuntu 24.04 LTS DigitalOcean Droplet.

The application uses a hybrid, high-performance architecture:
*   **Web Application**: Laravel 12 (PHP 8.3 + Nginx + MySQL)
*   **Recommendation Service**: Python Flask Microservice (utilizing Pandas, Scikit-Learn, and Scikit-Surprise matrix factorization models)
*   **Process Managers**: Supervisor (running Laravel database queue workers and Gunicorn for the Flask service)

---

## 1. Prerequisites & Droplet Provisioning

### Droplet Size Recommendation
> [!IMPORTANT]
> Because training the recommendation system involves SVD (Singular Value Decomposition) and cosine similarity computations over user interaction tables, we recommend a Droplet with **at least 2 GB RAM** (e.g., the $12/month Premium Intel/AMD with SSD option). A 1 GB RAM Droplet may run out of memory during SVD model training.

### Step 1: Create and Access Your Droplet
1.  **Deploy a Droplet** in your DigitalOcean account:
    *   **OS**: Ubuntu 24.04 LTS (x64)
    *   **Size**: Basic Plan (Intel/AMD with 2 GB RAM, 1 CPU, 50 GB NVMe/SSD)
    *   **Authentication**: SSH Keys (highly recommended for security)
2.  **Access your Droplet** via terminal:
    ```bash
    ssh root@your_droplet_ip
    ```
3.  **Update and upgrade system packages**:
    ```bash
    sudo apt update && sudo apt upgrade -y
    ```
4.  **Configure Firewall (UFW)**:
    ```bash
    sudo ufw allow OpenSSH
    sudo ufw allow 'Nginx Full'
    sudo ufw enable
    ```

---

## 2. Installing the Core Stack

### A. PHP 8.3 & Extensions
Laravel 12 requires PHP 8.2+. We will install PHP 8.3 for maximum performance. Add the Ondřej Surý PPA:
```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

Install PHP 8.3, FPM, CLI, and all required extension packages:
```bash
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-xml php8.3-mbstring php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-readline php8.3-sqlite3
```

### B. Nginx, MySQL & Supervisor
Install Nginx web server, MySQL database server, Supervisor process manager, Git, and utilities:
```bash
sudo apt install -y nginx mysql-server supervisor git unzip
```

Secure your MySQL installation:
```bash
sudo mysql_secure_installation
```
Follow the prompts to configure password policies, remove anonymous users, disable remote root login, and remove the test database.

### C. Node.js 20.x & Composer
Install Composer (PHP dependency manager):
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Install Node.js 20.x (LTS) for compiling front-end assets via Vite:
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### D. Python Build Dependencies
> [!IMPORTANT]
> The recommender service requires compiling C extensions for packages like `scikit-surprise` and `numpy`. You **must** install build-essential, C++ compiler headers, and Python development headers before installing python packages.

```bash
sudo apt install -y python3 python3-pip python3-venv python3-dev build-essential g++ liblapack-dev gfortran
```

---

## 3. Cloning & Configuring the Application

### A. Setting Directory & Cloning
Create the target directory and clone your repository. Ensure ownership is granted to your current user:
```bash
sudo mkdir -p /var/www/musicsocial
sudo chown -R $USER:$USER /var/www/musicsocial
git clone -b f-1 https://github.com/HeinreichMorphine/musicsocial.git /var/www/musicsocial
```

### B. Environment File Configuration
Generate the configuration file by copying `.env.example`:
```bash
cd /var/www/musicsocial
cp .env.example .env
```

Open `.env` using nano:
```bash
nano .env
```

Configure the environment variables exactly as shown below:
```ini
APP_NAME="Reso"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com  # Update with your custom domain or droplet IP

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=musicsocial
DB_USERNAME=musicsocial_user
DB_PASSWORD=your_secure_db_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

QUEUE_CONNECTION=database
CACHE_STORE=database

# Python Recommender Integration
PYTHON_RECOMMENDER_URL=http://127.0.0.1:5000

# 3rd-Party API Keys & Integrations
SPOTIFY_CLIENT_ID=your_spotify_client_id
SPOTIFY_CLIENT_SECRET=your_spotify_client_secret
SPOTIFY_REDIRECT_URI=https://yourdomain.com/auth/spotify/callback

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

YOUTUBE_API_KEY=your_youtube_api_key
GEMINI_API_KEY=your_gemini_api_key

AUDIODB_BASE_URL=https://www.theaudiodb.com/api/v1/json/
AUDIODB_API_KEY=2 # Keep '2' as the default developer API key or replace with your custom tier key
MUSICBRAINZ_USER_AGENT="MusicSocialApp/1.0 ( your_email@example.com )"
LASTFM_API_KEY=your_lastfm_api_key

DISCOGS_CONSUMER_KEY=your_discogs_consumer_key
DISCOGS_CONSUMER_SECRET=your_discogs_consumer_secret
DISCOGS_TOKEN=https://api.discogs.com/oauth/request_token
```

### C. Database and User Creation
Log into MySQL:
```bash
sudo mysql
```

Run the following SQL statements to set up the database and user:
```sql
CREATE DATABASE musicsocial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'musicsocial_user'@'127.0.0.1' IDENTIFIED BY 'your_secure_db_password';
GRANT ALL PRIVILEGES ON musicsocial.* TO 'musicsocial_user'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

### D. Installing Packages & Compiling Assets
1.  Install PHP packages using Composer:
    ```bash
    composer install --no-dev --optimize-autoloader
    ```
2.  Generate the Laravel encryption key:
    ```bash
    php artisan key:generate
    ```
3.  Install NPM packages and compile Vite assets:
    ```bash
    npm install
    npm run build
    ```
4.  Run database migrations and seeders:
    ```bash
    php artisan migrate --force
    # Optional: Run seeders if setting up fresh demo data
    # php artisan db:seed --force
    ```

### E. Set Directory Permissions
Nginx needs permission to write to `storage` and `bootstrap/cache`:
```bash
sudo chown -R www-data:www-data /var/www/musicsocial/storage /var/www/musicsocial/bootstrap/cache
sudo chmod -R 775 /var/www/musicsocial/storage /var/www/musicsocial/bootstrap/cache
```

---

## 4. Setting Up the Python Recommender Service

1.  Navigate to the recommender service directory:
    ```bash
    cd /var/www/musicsocial/recommender_service
    ```
2.  Create a Python virtual environment:
    ```bash
    python3 -m venv venv
    ```
3.  Activate the virtual environment:
    ```bash
    source venv/bin/activate
    ```
4.  Install requirements (this compiles C extensions for packages like `scikit-surprise`):
    ```bash
    pip install --upgrade pip
    pip install -r requirements.txt
    ```
5.  Perform initial model training to create starting matrix factorization weights and cache files:
    ```bash
    python train.py
    ```
6.  Deactivate the virtual environment:
    ```bash
    deactivate
    ```

---

## 5. Configuring Web Server & Services

### A. Nginx Configuration
Copy the provided Nginx configuration file to Nginx's site pool:
```bash
sudo cp /var/www/musicsocial/deployment/nginx/musicsocial.conf /etc/nginx/sites-available/musicsocial
```

Enable the site configuration by creating a symlink:
```bash
sudo ln -s /etc/nginx/sites-available/musicsocial /etc/nginx/sites-enabled/
```

Remove the default site configuration:
```bash
sudo rm -f /etc/nginx/sites-enabled/default
```

Test the configuration and reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### B. SSL Encryption via Let's Encrypt (Certbot)
To secure the site with HTTPS:
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```
Follow the interactive prompts to enable redirecting HTTP traffic to HTTPS.

### C. Supervisor Setup (Queues & Flask Service)
Copy the Supervisor configurations from the project:
```bash
sudo cp /var/www/musicsocial/deployment/supervisor/*.conf /etc/supervisor/conf.d/
```

Reread and update supervisor config to launch the services:
```bash
sudo supervisorctl reread
sudo supervisorctl update
```

Check the status of both processes:
```bash
sudo supervisorctl status
```
You should see:
```text
musicsocial-recommender                  RUNNING   pid 1234, uptime 0:10:00
musicsocial-worker:musicsocial-worker_00 RUNNING   pid 1235, uptime 0:10:00
musicsocial-worker:musicsocial-worker_01 RUNNING   pid 1236, uptime 0:10:00
```

---

## 6. Automation & Cron Setup

### A. Laravel Scheduler
Laravel uses a single scheduler cron job to run periodic tasks (like pruning expired sessions, cleaning caches, etc.).
Open the system crontab:
```bash
crontab -e
```
Add the following line at the end:
```text
* * * * * cd /var/www/musicsocial && php artisan schedule:run >> /dev/null 2>&1
```

### B. Automatic Recommender Model Retraining
To keep recommendation caches fresh, schedule the python training script to run daily at 2:00 AM.
Open the crontab:
```bash
crontab -e
```
Add the daily cron:
```text
0 2 * * * /var/www/musicsocial/recommender_service/venv/bin/python /var/www/musicsocial/recommender_service/train.py >> /var/www/musicsocial/storage/logs/recommender_cron.log 2>&1
```

---

## 7. Verification & Troubleshooting

### Check Python Service Logs
Verify that the Gunicorn Flask service starts up and binds to `127.0.0.1:5000` without errors:
```bash
tail -n 50 /var/www/musicsocial/storage/logs/recommender.out.log
tail -n 50 /var/www/musicsocial/storage/logs/recommender.error.log
```

### Test Recommender Connection
Test if the local Flask endpoint responds correctly from the Droplet:
```bash
curl http://127.0.0.1:5000/
```
You should get:
```text
Recommendation Service is running!
```

### Test Stats Endpoint
Check that the model has successfully trained and is serving statistics:
```bash
curl http://127.0.0.1:5000/stats
```
Expected output:
```json
{
  "algo_version": "3.8.2-ALPHA",
  "last_train_time": "2026-06-19 15:30:00",
  "model_file_exists": true,
  "songs_in_model": 382,
  "total_interactions": 14820,
  "users_in_model": 154
}
```

### Laravel Logs
For database issues, API limits, or general PHP errors:
```bash
tail -n 100 /var/www/musicsocial/storage/logs/laravel.log
```

---

## 8. Accessing the Server & Updating from Git

If you need to log back into your server from your Windows terminal to update the codebase, follow these steps:

### Step 1: Connect via SSH from Windows PowerShell
Open Windows PowerShell on your machine and run the following command using your private SSH key:
```powershell
ssh -i "C:\Users\kiddp\Desktop\FYP\id_ed25519" root@168.144.108.176
```

### Step 2: Navigate to the Project Directory
Once logged in, move to the folder where the website is located:
```bash
cd /var/www/musicsocial
```

### Step 3: Pull the Latest Changes from GitHub
Fetch and apply the latest updates from your `f-1` branch:
```bash
git pull origin f-1
```

### Step 4: Clear Laravel Caches (Recommended)
Always clear the views and configs cache after updating to ensure the changes take effect immediately:
```bash
# Clear blade view cache
php artisan view:clear

# Clear application config cache
php artisan config:clear
```

---
**Congratulations!** Reso is now fully configured and running on your DigitalOcean Droplet with automated queue workers and real-time recommendation caching.

