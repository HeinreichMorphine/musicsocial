@echo off
echo [MusicSocial] Initiating Recommendation Engine Retraining...
powershell -Command "Invoke-RestMethod -Method Post -Uri http://localhost:5000/retrain"
echo.
echo Retraining process completed.
pause
