<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\TwoFactorController;

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

// Chatbot routes
Route::middleware('auth')->group(function () {
    Route::post('/chatbot/query', [ChatbotController::class, 'query'])->name('chatbot.query');
    Route::get('/chatbot/test', function () {
        return view('chatbot-test');
    })->name('chatbot.test');
});

// Two-Factor Authentication routes
Route::middleware('auth')->group(function () {
    Route::get('/2fa/challenge', [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::get('/2fa/logout', [TwoFactorController::class, 'logout'])->name('2fa.logout');
});
