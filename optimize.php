#!/usr/bin/env php
<?php

/**
 * Performance Optimization Script
 * 
 * This script applies Laravel optimization commands to improve performance.
 * Run this after deployment or when performance issues occur.
 */

echo "\n═══════════════════════════════════════════════\n";
echo "   LARAVEL PERFORMANCE OPTIMIZATION SCRIPT\n";
echo "═══════════════════════════════════════════════\n\n";

// Check if Laravel artisan exists
if (!file_exists(__DIR__ . '/artisan')) {
    echo "❌ Error: artisan file not found. Run this script from Laravel root directory.\n";
    exit(1);
}

$commands = [
    [
        'title' => 'Clearing old cache',
        'command' => 'php artisan cache:clear',
        'description' => 'Clearing application cache'
    ],
    [
        'title' => 'Clearing configuration cache',
        'command' => 'php artisan config:clear',
        'description' => 'Clearing configuration cache'
    ],
    [
        'title' => 'Clearing route cache',
        'command' => 'php artisan route:clear',
        'description' => 'Clearing route cache'
    ],
    [
        'title' => 'Clearing view cache',
        'command' => 'php artisan view:clear',
        'description' => 'Clearing compiled views'
    ],
    [
        'title' => 'Running database migrations',
        'command' => 'php artisan migrate --force',
        'description' => 'Applying database migrations'
    ],
    [
        'title' => 'Caching configuration',
        'command' => 'php artisan config:cache',
        'description' => 'Caching configuration files'
    ],
    [
        'title' => 'Caching routes',
        'command' => 'php artisan route:cache',
        'description' => 'Caching application routes'
    ],
    [
        'title' => 'Caching views',
        'command' => 'php artisan view:cache',
        'description' => 'Compiling and caching views'
    ],
    [
        'title' => 'Optimizing autoloader',
        'command' => 'composer dump-autoload -o',
        'description' => 'Generating optimized autoload files'
    ],
    [
        'title' => 'Caching Filament components',
        'command' => 'php artisan filament:cache-components',
        'description' => 'Caching Filament components',
        'optional' => true
    ],
    [
        'title' => 'Optimizing Filament',
        'command' => 'php artisan filament:optimize',
        'description' => 'Optimizing Filament resources',
        'optional' => true
    ],
];

$errors = [];
$warnings = [];

foreach ($commands as $task) {
    echo "→ {$task['description']}... ";
    
    $output = [];
    $returnCode = 0;
    exec($task['command'] . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Done\n";
    } else {
        if (isset($task['optional']) && $task['optional']) {
            echo "⚠ Skipped (optional)\n";
            $warnings[] = $task['title'];
        } else {
            echo "✗ Failed\n";
            $errors[] = [
                'task' => $task['title'],
                'output' => implode("\n", $output)
            ];
        }
    }
}

echo "\n═══════════════════════════════════════════════\n";

if (empty($errors)) {
    echo "✓ All optimizations completed successfully!\n";
} else {
    echo "⚠ Some optimizations failed:\n\n";
    foreach ($errors as $error) {
        echo "  • {$error['task']}\n";
        echo "    " . substr($error['output'], 0, 100) . "...\n\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠ Optional optimizations skipped:\n";
    foreach ($warnings as $warning) {
        echo "  • $warning\n";
    }
}

echo "\n═══════════════════════════════════════════════\n";
echo "\nRECOMMENDATIONS:\n";
echo "1. Configure Redis for caching (update CACHE_DRIVER=redis in .env)\n";
echo "2. Run queue workers: php artisan queue:work\n";
echo "3. Build production assets: npm run build\n";
echo "4. Review PERFORMANCE_OPTIMIZATION.md for more tips\n";
echo "\n";

exit(empty($errors) ? 0 : 1);
