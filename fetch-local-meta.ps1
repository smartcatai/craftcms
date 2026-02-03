# PowerShell script to fetch content from URL and log to console

./deploy-local.ps1
# Define the URL
$url = "http://localhost:8080/api/smartcat/meta"

try {
    # Make the HTTP request
    Write-Host "Fetching content from: $url" -ForegroundColor Yellow
    Write-Host "=" * 50
    
    $response = Invoke-RestMethod -Uri $url -Method Get
    
    # Log the response to console
    Write-Host "Response received successfully!" -ForegroundColor Green
    Write-Host "Content:" -ForegroundColor Cyan
    Write-Host "=" * 50
    
    # Convert to JSON for pretty formatting if it's an object
    if ($response -is [PSCustomObject] -or $response -is [Array]) {
        $jsonOutput = $response | ConvertTo-Json -Depth 10
        Write-Host $jsonOutput
    } else {
        Write-Host $response
    }
    
    Write-Host "=" * 50
    Write-Host "Script completed successfully!" -ForegroundColor Green
}
catch {
    Write-Host "Error occurred while fetching content:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host "Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
}