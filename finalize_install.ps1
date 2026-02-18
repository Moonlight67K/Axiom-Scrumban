$tempDir = "_temp"
$destDir = "."

Write-Host "Moving files from $tempDir to $destDir..."

# Move all items, including hidden ones
Get-ChildItem -Path $tempDir -Force | ForEach-Object {
    $destPath = Join-Path -Path $destDir -ChildPath $_.Name
    if (Test-Path $destPath) {
        Write-Warning "Destination $destPath already exists. Skipping."
    } else {
        Move-Item -Path $_.FullName -Destination $destDir -Force
    }
}

Write-Host "Files moved."

# Clean up temp dir
if ((Get-ChildItem $tempDir -Force).Count -eq 0) {
    Remove-Item -Path $tempDir -Force
    Write-Host "Removed empty temp directory."
} else {
    Write-Warning "Temp directory not empty. Please check manually."
}

# Run setup
./setup_setup.ps1
