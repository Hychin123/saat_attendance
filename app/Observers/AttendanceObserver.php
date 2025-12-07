<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Services\TelegramService;

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
        // Send check-in notification when attendance is created
        if ($attendance->time_in && $attendance->user->telegram_notifications) {
            $this->telegramService->sendCheckInNotification(
                $attendance->user,
                $attendance
            );
        }
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        // Send check-out notification when time_out is added
        if ($attendance->time_out && 
            $attendance->isDirty('time_out') && 
            $attendance->user->telegram_notifications) {
            
            $this->telegramService->sendCheckOutNotification(
                $attendance->user,
                $attendance
            );
        }
    }
}
