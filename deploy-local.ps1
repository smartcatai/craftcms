# PowerShell script to copy plugin source to local Craft projects
# Source and destination paths
$sourcePath = "C:\Smartcat\craftcms\src"
$destinationPaths = @(
    "C:\Smartcat\craft3\my-craft-project\vendor\smartcat-ai\craft-smartcat-integration\src",
    "C:\Smartcat\craft2\my-craft-project\vendor\smartcat-ai\craft-smartcat-integration\src",
    "C:\Smartcat\craft-cms-stage\vendor\smartcat-ai\craft-smartcat-integration\src"
)

# Check if source directory exists
if (-Not (Test-Path -Path $sourcePath)) {
    Write-Error "Source directory does not exist: $sourcePath"
    exit 1
}

try {
    $copiedTo = @()

    foreach ($destinationPath in $destinationPaths) {
        if (-Not (Test-Path -Path (Split-Path -Parent $destinationPath))) {
            Write-Host "Skipping destination (parent path does not exist): $destinationPath" -ForegroundColor Yellow
            continue
        }

        if (-Not (Test-Path -Path $destinationPath)) {
            Write-Host "Creating destination directory: $destinationPath"
            New-Item -ItemType Directory -Path $destinationPath -Force | Out-Null
        }

        Write-Host "Copying files from $sourcePath to $destinationPath..."
        Copy-Item -Path "$sourcePath\*" -Destination $destinationPath -Recurse -Force

        $copiedItems = Get-ChildItem -Path $destinationPath -Recurse
        Write-Host "Copied to: $destinationPath (items: $($copiedItems.Count))" -ForegroundColor Green
        $copiedTo += $destinationPath
    }

    if ($copiedTo.Count -eq 0) {
        throw "No valid destination paths found."
    }

    Write-Host "Copy operation completed successfully!" -ForegroundColor Green
}
catch {
    Write-Error "An error occurred during the copy operation: $($_.Exception.Message)"
    exit 1
}