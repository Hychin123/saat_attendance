# Performance Optimization Script for Windows
# Run this script with PowerShell to optimize Laravel application

Write-Host "`n═══════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   LARAVEL PERFORMANCE OPTIMIZATION SCRIPT" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════`n" -ForegroundColor Cyan

# Check if artisan exists
if (-not (Test-Path ".\artisan")) {
    Write-Host "❌ Error: artisan file not found. Run this script from Laravel root directory." -ForegroundColor Red
    exit 1
}

$commands = @(
    @{
        title = "Clearing old cache"
        command = "php artisan cache:clear"
        description = "Clearing application cache"
    },
    @{
        title = "Clearing configuration cache"
        command = "php artisan config:clear"
        description = "Clearing configuration cache"
    },
    @{
        title = "Clearing route cache"
        command = "php artisan route:clear"
        description = "Clearing route cache"
    },
    @{
        title = "Clearing view cache"
        command = "php artisan view:clear"
        description = "Clearing compiled views"
    },
    @{
        title = "Running database migrations"
        command = "php artisan migrate --force"
        description = "Applying database migrations (including performance indexes)"
    },
    @{
        title = "Caching configuration"
        command = "php artisan config:cache"
        description = "Caching configuration files"
    },
    @{
        title = "Caching routes"
        command = "php artisan route:cache"
        description = "Caching application routes"
    },
    @{
        title = "Caching views"
        command = "php artisan view:cache"
        description = "Compiling and caching views"
    },
    @{
        title = "Optimizing autoloader"
        command = "composer dump-autoload -o"
        description = "Generating optimized autoload files"
    },
    @{
        title = "Caching Filament components"
        command = "php artisan filament:cache-components"
        description = "Caching Filament components"
        optional = $true
    },
    @{
        title = "Optimizing Filament"
        command = "php artisan filament:optimize"
        description = "Optimizing Filament resources"
        optional = $true
    }
)

$errors = @()
$warnings = @()

foreach ($task in $commands) {
    Write-Host "→ $($task.description)... " -NoNewline
    
    try {
        $output = Invoke-Expression $task.command 2>&1
        $success = $LASTEXITCODE -eq 0
        
        if ($success) {
            Write-Host "✓ Done" -ForegroundColor Green
        } else {
            if ($task.optional) {
                Write-Host "⚠ Skipped (optional)" -ForegroundColor Yellow
                $warnings += $task.title
            } else {
                Write-Host "✗ Failed" -ForegroundColor Red
                $errors += @{
                    task = $task.title
                    output = $output
                }
            }
        }
    } catch {
        if ($task.optional) {
            Write-Host "⚠ Skipped (optional)" -ForegroundColor Yellow
            $warnings += $task.title
        } else {
            Write-Host "✗ Failed" -ForegroundColor Red
            $errors += @{
                task = $task.title
                output = $_.Exception.Message
            }
        }
    }
}

Write-Host "`n═══════════════════════════════════════════════" -ForegroundColor Cyan

if ($errors.Count -eq 0) {
    Write-Host "✓ All optimizations completed successfully!" -ForegroundColor Green
} else {
    Write-Host "⚠ Some optimizations failed:`n" -ForegroundColor Yellow
    foreach ($error in $errors) {
        Write-Host "  • $($error.task)" -ForegroundColor Red
    }
}

if ($warnings.Count -gt 0) {
    Write-Host "`n⚠ Optional optimizations skipped:" -ForegroundColor Yellow
    foreach ($warning in $warnings) {
        Write-Host "  • $warning" -ForegroundColor Yellow
    }
}

Write-Host "`n═══════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "`nRECOMMENDATIONS:" -ForegroundColor Cyan
Write-Host "1. Configure Redis for caching (update CACHE_DRIVER=redis in .env)" -ForegroundColor White
Write-Host "2. Run queue workers: php artisan queue:work" -ForegroundColor White
Write-Host "3. Build production assets: npm run build" -ForegroundColor White
Write-Host "4. Review PERFORMANCE_OPTIMIZATION.md for more tips" -ForegroundColor White
Write-Host ""

if ($errors.Count -gt 0) {
    exit 1
} else {
    exit 0
}
