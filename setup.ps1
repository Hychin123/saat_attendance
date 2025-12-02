# Quick Start Script for SAAT Attendance System
# Run this script after cloning the repository

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "SAAT Attendance System - Quick Start" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Install Composer Dependencies
Write-Host "[1/8] Installing Composer dependencies..." -ForegroundColor Yellow
composer install

# Step 2: Install required packages
Write-Host "[2/8] Installing QR Code package..." -ForegroundColor Yellow
composer require simplesoftwareio/simple-qrcode

Write-Host "[2/8] Installing Excel export package..." -ForegroundColor Yellow
composer require pxlrbt/filament-excel

# Step 3: Copy .env file
Write-Host "[3/8] Setting up environment file..." -ForegroundColor Yellow
if (!(Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Host "Created .env file" -ForegroundColor Green
} else {
    Write-Host ".env file already exists" -ForegroundColor Green
}

# Step 4: Generate key
Write-Host "[4/8] Generating application key..." -ForegroundColor Yellow
php artisan key:generate

# Step 5: Database check
Write-Host "[5/8] Database Setup" -ForegroundColor Yellow
Write-Host "Please ensure your database is configured in .env file" -ForegroundColor Cyan
Write-Host "Press any key to continue after configuring database..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# Step 6: Run migrations
Write-Host "[6/8] Running database migrations..." -ForegroundColor Yellow
php artisan migrate

# Step 7: Ask about seeding
Write-Host "[7/8] Do you want to seed sample data? (Y/N)" -ForegroundColor Yellow
$seed = Read-Host
if ($seed -eq 'Y' -or $seed -eq 'y') {
    php artisan db:seed --class=AttendanceSystemSeeder
    Write-Host "Sample data seeded successfully!" -ForegroundColor Green
    Write-Host "Admin Login: admin@example.com / password" -ForegroundColor Cyan
}

# Step 8: Storage link
Write-Host "[8/8] Creating storage link..." -ForegroundColor Yellow
php artisan storage:link

# Step 9: Install NPM dependencies
Write-Host "Installing NPM dependencies..." -ForegroundColor Yellow
npm install

Write-Host "Building assets..." -ForegroundColor Yellow
npm run build

Write-Host ""
Write-Host "==================================" -ForegroundColor Green
Write-Host "Installation Complete!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Create Filament admin user: php artisan make:filament-user" -ForegroundColor White
Write-Host "2. Start development server: php artisan serve" -ForegroundColor White
Write-Host "3. Visit: http://localhost:8000/admin" -ForegroundColor White
Write-Host ""
Write-Host "Additional URLs:" -ForegroundColor Cyan
Write-Host "- QR Code: http://localhost:8000/attendance/qr" -ForegroundColor White
Write-Host "- Scan Page: http://localhost:8000/attendance/scan" -ForegroundColor White
Write-Host ""
Write-Host "Happy coding! 🚀" -ForegroundColor Yellow
