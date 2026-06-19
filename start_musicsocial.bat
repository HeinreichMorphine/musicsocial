@echo off
REM Force path to project directory to avoid "system cannot find path" errors
cd /d "C:\laragon\www\musicsocial-main"
echo Starting MusicSocial Development Environment...

REM 1. Start Laravel Backend (Port 8000)
echo Starting Laravel Server...
start "MusicSocial Backend (Laravel)" php artisan serve

REM 2. Start Frontend (Vite)
echo Starting Vite Frontend...
start "MusicSocial Frontend (Vite)" npm run dev

REM 3. Start Python Recommender Service (Port 5000)
echo Starting Python Recommender Service...
cd recommender_service
start "MusicSocial Recommender (Python)" cmd /c "call start_recommender.bat"

REM 4. Open Web Dashboards
echo Waiting for Recommender API to initialize...
timeout /t 3 /nobreak > nul
echo Starting Developer Web Dashboards...
start http://localhost/musicsocial-main/accuracy_suite_screenshot.html
start http://localhost/musicsocial-main/algo_test_suite.html
cd ..

echo.
echo ======================================================
echo ALL SERVICES STARTED!
echo ======================================================
echo Backend:      http://127.0.0.1:8000
echo Frontend:     http://localhost:5173
echo Recommender:  http://127.0.0.1:5000
echo User Audit:   http://localhost/musicsocial-main/accuracy_suite_screenshot.html
echo Global Suite: http://localhost/musicsocial-main/algo_test_suite.html
echo.
echo YouTube embeds (official music videos) need a PUBLIC URL on local dev.
echo   1. Laragon: Menu - www - Share - musicsocial-main (Ngrok)
echo   2. Open the https://....ngrok-free.app URL (not 127.0.0.1)
echo   3. In .env set: YOUTUBE_EMBED_ORIGIN=https://your-ngrok-url.ngrok-free.app
echo   4. Run: php artisan config:clear
echo ======================================================
echo.
echo Retraining Command:
echo Invoke-RestMethod -Method Post -Uri http://localhost:5000/retrain
echo ======================================================
echo.
pause
