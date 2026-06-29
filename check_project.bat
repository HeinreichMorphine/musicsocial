@echo off
SETLOCAL EnableDelayedExpansion

:: Set the project root path
set "PROJECT_ROOT=C:\laragon\www\musicsocial-main"
cd /d "%PROJECT_ROOT%"

echo ===================================================
echo Starting Local Project Checks
echo Directory: %PROJECT_ROOT%
echo ===================================================

:: 1. Run Knip (JavaScript / TypeScript Linter)
echo.
echo [1/2] Running Knip Dependency Check...
if exist "node_modules" (
    call npm run knip
) else (
    echo Warning: node_modules folder not found. Run 'npm install' first.
)

:: 2. Run Medusa SAST Scanner
echo.
echo [2/2] Running Medusa Security Scan...

:: Create virtual environment if it doesn't exist
if not exist "medusa-env" (
    echo Creating Python virtual environment medusa-env...
    python -m venv medusa-env
)

:: Install/Update medusa-security inside the environment
echo Verifying medusa-security installation...
call .\medusa-env\Scripts\python.exe -m pip install --upgrade pip
call .\medusa-env\Scripts\pip.exe install medusa-security

:: Run the scan on the current directory
echo Executing code scanner...
call .\medusa-env\Scripts\medusa.exe scan .

echo ===================================================
echo Project checks completed.
echo ===================================================
pause
