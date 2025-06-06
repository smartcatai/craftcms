# PowerShell script to copy files and directories
# Source and destination paths
$sourcePath = "d:\Projects\Smartcat\CraftCms\src"
$destinationPath = "c:\linux\my-craft-project\vendor\smartcat-ai\craft-smartcat-integration\src"

# Check if source directory exists
if (-Not (Test-Path -Path $sourcePath)) {
    Write-Error "Source directory does not exist: $sourcePath"
    exit 1
}

# Create destination directory if it doesn't exist
if (-Not (Test-Path -Path $destinationPath)) {
    Write-Host "Creating destination directory: $destinationPath"
    New-Item -ItemType Directory -Path $destinationPath -Force
}

try {
    Write-Host "Copying files from $sourcePath to $destinationPath..."
    
    # Copy all files and subdirectories recursively with Force as default
    Copy-Item -Path "$sourcePath\*" -Destination $destinationPath -Recurse -Force
    
    Write-Host "Copy operation completed successfully!" -ForegroundColor Green
    
    # Display summary
    $copiedItems = Get-ChildItem -Path $destinationPath -Recurse
    Write-Host "Total items copied: $($copiedItems.Count)" -ForegroundColor Green
}
catch {
    Write-Error "An error occurred during the copy operation: $($_.Exception.Message)"
    exit 1
}