<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return view('welcome');
});

// Login route - redirect to Filament admin
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Attendance routes
Route::prefix('attendance')->group(function () {
    Route::get('/qr', [AttendanceController::class, 'showQrCode'])->name('attendance.qr');
    
    // Protected routes - require authentication
    Route::middleware('auth')->group(function () {
        Route::get('/scan', [AttendanceController::class, 'showScanPage'])->name('attendance.scan');
        Route::post('/process', [AttendanceController::class, 'processAttendance'])->name('attendance.process');
        Route::get('/status', [AttendanceController::class, 'getStatus'])->name('attendance.status');
    });
});
