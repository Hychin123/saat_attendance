# Sales Management System - Installation Script
# Encoding: UTF-8

Write-Host "================================================================" -ForegroundColor Cyan
Write-Host "        Sales Management System - Installation" -ForegroundColor Cyan
Write-Host "================================================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Run Migrations
Write-Host "[Step 1/3] Running database migrations..." -ForegroundColor Yellow
try {
    php artisan migrate --force
    Write-Host "[SUCCESS] Migrations completed successfully!" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Migration failed: $_" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 2: Seed Permissions
Write-Host "[Step 2/3] Seeding sales permissions..." -ForegroundColor Yellow
try {
    php artisan db:seed --class=SalesPermissionsSeeder --force
    Write-Host "[SUCCESS] Permissions seeded successfully!" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Seeding failed: $_" -ForegroundColor Red
    Write-Host "[WARNING] You may need to create the admin role first" -ForegroundColor Yellow
}
Write-Host ""

# Step 3: Clear Cache
Write-Host "[Step 3/3] Clearing application cache..." -ForegroundColor Yellow
try {
    php artisan optimize:clear
    Write-Host "[SUCCESS] Cache cleared successfully!" -ForegroundColor Green
} catch {
    Write-Host "[WARNING] Cache clear failed (non-critical): $_" -ForegroundColor Yellow
}
Write-Host ""

# Summary
Write-Host "================================================================" -ForegroundColor Green
Write-Host "              Installation Complete!" -ForegroundColor Green
Write-Host "================================================================" -ForegroundColor Green
Write-Host ""

Write-Host "What was installed:" -ForegroundColor Cyan
Write-Host "  [+] 4 Database tables (sales, sale_items, payments, commissions)" -ForegroundColor White
Write-Host "  [+] 19 Sales permissions" -ForegroundColor White
Write-Host "  [+] 4 Models (Sale, SaleItem, Payment, Commission)" -ForegroundColor White
Write-Host "  [+] 3 Filament Resources (Sales, Payments, Commissions)" -ForegroundColor White
Write-Host "  [+] Business logic observer (SaleObserver)" -ForegroundColor White
Write-Host ""

Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "  1. Access your Filament admin panel" -ForegroundColor White
Write-Host "  2. Look for 'Sales Management' group in the sidebar" -ForegroundColor White
Write-Host "  3. Create your first sale!" -ForegroundColor White
Write-Host ""

Write-Host "Documentation:" -ForegroundColor Cyan
Write-Host "  - SALES_QUICKSTART.md - Quick start guide" -ForegroundColor White
Write-Host "  - SALES_DOCUMENTATION.md - Full documentation" -ForegroundColor White
Write-Host "  - SALES_IMPLEMENTATION_SUMMARY.md - Implementation details" -ForegroundColor White
Write-Host ""

Write-Host "Quick Test Workflow:" -ForegroundColor Cyan
Write-Host "  1. Create a sale (Status: PENDING)" -ForegroundColor White
Write-Host "  2. Add a payment (Status changes to DEPOSITED)" -ForegroundColor White
Write-Host "  3. Change status to PROCESSING (Stock reduces automatically)" -ForegroundColor White
Write-Host "  4. Change to READY" -ForegroundColor White
Write-Host "  5. Add balance payment and set COMPLETED (Commission generated)" -ForegroundColor White
Write-Host ""

Write-Host "Need help? Check SALES_QUICKSTART.md" -ForegroundColor Yellow
Write-Host ""
Write-Host "Happy Selling!" -ForegroundColor Green
