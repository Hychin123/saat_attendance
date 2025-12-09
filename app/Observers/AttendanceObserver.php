<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class AttendanceObserver
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        try {
            // Load relationships
            $attendance->load('user.role', 'shift');
            
            // Always send to channel for all check-ins
            if ($attendance->time_in && $attendance->user) {
                Log::info('Processing check-in for user: ' . $attendance->user->name);
                
                // Send to channel first (always)
                $this->telegramService->sendCheckInToChannel($attendance);
                
                // Also send personal notification if user has Telegram configured
                if ($attendance->user->telegram_notifications && 
                    $attendance->user->telegram_chat_id) {
                    $this->telegramService->sendCheckInNotification(
                        $attendance->user,
                        $attendance
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Observer Check-in Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        try {
            // Load relationships
            $attendance->load('user.role', 'shift');
            
            // Send check-out notification when time_out is added
            if ($attendance->time_out && 
                $attendance->wasChanged('time_out') && 
                $attendance->user) {
                
                Log::info('Processing check-out for user: ' . $attendance->user->name);
                
                // Send to channel first (always)
                $this->telegramService->sendCheckOutToChannel($attendance);
                
                // Also send personal notification if user has Telegram configured
                if ($attendance->user->telegram_notifications && 
                    $attendance->user->telegram_chat_id) {
                    $this->telegramService->sendCheckOutNotification(
                        $attendance->user,
                        $attendance
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Observer Check-out Error: ' . $e->getMessage());
        }
    }
}
