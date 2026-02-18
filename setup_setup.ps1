# Setup Script for Axiom Scrumban (Windows)

Write-Host "Setting up Axiom Scrumban..."

# 1. Environment File
if (-not (Test-Path .env)) {
    Write-Host "Creating .env file..."
    Copy-Item .env.example .env
}

# 2. Key Generation
Write-Host "Generating App Key..."
php artisan key:generate

# 3. Storage Link
Write-Host "Linking Storage..."
php artisan storage:link

# 4. Run Migrations (Interactive)
Write-Host "Running Migrations..."
php artisan migrate

Write-Host "Setup Complete! Don't forget to configure your .env file for PostgreSQL."
