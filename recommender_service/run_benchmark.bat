@echo off
cd /d %~dp0
echo Activating virtual environment...
if exist venv\Scripts\activate.bat (
    call venv\Scripts\activate
) else (
    echo "venv not found. checking for global python..."
)

echo Running Benchmark Script...
python benchmark_model.py

pause
