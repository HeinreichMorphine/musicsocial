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
start "MusicSocial Recommender (Python)" cmd /k "call start_recommender.bat"
cd ..

echo.
echo All services started!
echo Backend:   http://127.0.0.1:8000
echo Frontend:  http://localhost:5173
echo Recommender: http://127.0.0.1:5000
echo.
pause
