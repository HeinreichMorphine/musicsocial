@echo off

:: =============================================================
:: Robust Master Launch Script
:: Hardcodes the path to ensure it works even if copied to Desktop
:: =============================================================

set "PROJECT_ROOT=c:\laragon\www\musicsocial-main"

:: Check if the project actually exists at this path
if not exist "%PROJECT_ROOT%" (
    echo Error: Could not find project at %PROJECT_ROOT%
    echo Please edit this script to set the correct PROJECT_ROOT.
    pause
    exit /b
)

cd /d "%PROJECT_ROOT%"

echo ===================================================
echo   Starting ALL MusicSocial Services
echo   Root: %PROJECT_ROOT%
echo ===================================================
echo.

:: 1. Start Laravel Backend
echo [1/3] Launching Laravel (php artisan serve)...
:: /D specifies the starting directory for the new window
start "MusicSocial Backend (Laravel)" /D "%PROJECT_ROOT%" cmd /k "php artisan serve"

:: 2. Start Vite Frontend
echo [2/3] Launching Vite (npm run dev)...
start "MusicSocial Frontend (Vite)" /D "%PROJECT_ROOT%" cmd /k "npm run dev"

:: 3. Start Python Recommender
echo [3/3] Launching Recommender Service...
:: Set dir to recommender_service so it finds requirements.txt and app.py
start "MUSIC RECSYS - LOGS & DEBUGGER (DO NOT CLOSE)" /D "%PROJECT_ROOT%" cmd /k "cd recommender_service && call start_recommender.bat"

echo.
echo All services launched!
echo You can minimize this window (or close it, the others will stay open).
pause
