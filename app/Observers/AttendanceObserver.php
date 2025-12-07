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
            
            // Send check-in notification when attendance is created
            if ($attendance->time_in && 
                $attendance->user && 
                $attendance->user->telegram_notifications && 
                $attendance->user->telegram_chat_id) {
                
                Log::info('Sending check-in notification for user: ' . $attendance->user->name);
                
                $this->telegramService->sendCheckInNotification(
                    $attendance->user,
                    $attendance
                );
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
                $attendance->user && 
                $attendance->user->telegram_notifications && 
                $attendance->user->telegram_chat_id) {
                
                Log::info('Sending check-out notification for user: ' . $attendance->user->name);
                
                $this->telegramService->sendCheckOutNotification(
                    $attendance->user,
                    $attendance
                );
            }
        } catch (\Exception $e) {
            Log::error('Observer Check-out Error: ' . $e->getMessage());
        }
    }
}
