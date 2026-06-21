@echo off
cd /d "%~dp0"

echo ===================================================
echo   Starting Reso RecSys Service
echo ===================================================

:: Check if Python is available
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Python is not installed or not in your PATH.
    pause
    exit /b
)

:: Check if venv exists, if not create it
if not exist "venv" (
    echo [INFO] Virtual environment not found. Creating 'venv'...
    python -m venv venv
)

:: Activate venv
call venv\Scripts\activate.bat

:: Install/Upgrade dependencies
echo [INFO] Checking dependencies...
pip install -r requirements.txt

:: Start the server
echo.
echo [INFO] Starting Flask Server...
echo ===================================================
python app.py
echo ===================================================

pause
