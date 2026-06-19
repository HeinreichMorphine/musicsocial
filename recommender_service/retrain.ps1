Write-Host "[MusicSocial] Initiating Recommendation Engine Retraining..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Method Post -Uri "http://localhost:5000/retrain"
    Write-Host "Success: $($response.message)" -ForegroundColor Green
} catch {
    Write-Host "Error: Could not connect to the recommendation service. Ensure app.py is running on port 5000." -ForegroundColor Red
}
Read-Host "Press Enter to exit"
