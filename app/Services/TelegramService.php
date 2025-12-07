<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $telegram;
    protected $channelId;

    public function __construct()
    {
        $token = config('services.telegram.bot_token');
        $this->channelId = config('services.telegram.channel_id');
        
        if ($token) {
            $this->telegram = new BotApi($token);
        }
    }

    /**
     * Send check-in notification
     */
    public function sendCheckInNotification($user, $attendance)
    {
        if (!$this->telegram || !$user->telegram_chat_id) {
            return false;
        }

        try {
            $message = $this->formatCheckInMessage($user, $attendance);
            
            $this->telegram->sendMessage(
                $user->telegram_chat_id,
                $message,
                'HTML'
            );

            // Also send to channel if configured
            if ($this->channelId) {
                $this->telegram->sendMessage(
                    $this->channelId,
                    $message,
                    'HTML'
                );
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Check-in Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send check-out notification
     */
    public function sendCheckOutNotification($user, $attendance)
    {
        if (!$this->telegram || !$user->telegram_chat_id) {
            return false;
        }

        try {
            $message = $this->formatCheckOutMessage($user, $attendance);
            
            $this->telegram->sendMessage(
                $user->telegram_chat_id,
                $message,
                'HTML'
            );

            // Also send to channel if configured
            if ($this->channelId) {
                $this->telegram->sendMessage(
                    $this->channelId,
                    $message,
                    'HTML'
                );
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Check-out Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format check-in message
     */
    protected function formatCheckInMessage($user, $attendance)
    {
        $emoji = $attendance->is_late ? '⚠️' : '✅';
        $status = $attendance->is_late ? 'LATE' : 'ON TIME';
        
        $message = "{$emoji} <b>CHECK-IN ALERT</b>\n\n";
        $message .= "👤 <b>Employee:</b> {$user->name}\n";
        $message .= "🏢 <b>Role:</b> {$user->role->name}\n";
        $message .= "📅 <b>Date:</b> {$attendance->date->format('d M Y')}\n";
        $message .= "⏰ <b>Time In:</b> {$attendance->time_in->format('H:i')}\n";
        
        if ($attendance->shift) {
            $message .= "🔄 <b>Shift:</b> {$attendance->shift->name}\n";
            $message .= "📍 <b>Shift Time:</b> {$attendance->shift->start_time}\n";
        }
        
        $message .= "📊 <b>Status:</b> {$status}\n";
        
        if ($attendance->is_late) {
            $message .= "⏱️ <b>Late By:</b> {$attendance->late_minutes} minutes\n";
        }
        
        if ($attendance->notes) {
            $message .= "\n💬 <b>Notes:</b> {$attendance->notes}\n";
        }

        return $message;
    }

    /**
     * Format check-out message
     */
    protected function formatCheckOutMessage($user, $attendance)
    {
        $message = "🏁 <b>CHECK-OUT ALERT</b>\n\n";
        $message .= "👤 <b>Employee:</b> {$user->name}\n";
        $message .= "🏢 <b>Role:</b> {$user->role->name}\n";
        $message .= "📅 <b>Date:</b> {$attendance->date->format('d M Y')}\n";
        $message .= "⏰ <b>Time In:</b> {$attendance->time_in->format('H:i')}\n";
        $message .= "⏰ <b>Time Out:</b> {$attendance->time_out->format('H:i')}\n";
        
        if ($attendance->work_hours) {
            $hours = floor($attendance->work_hours);
            $minutes = round(($attendance->work_hours - $hours) * 60);
            $message .= "⌛ <b>Work Duration:</b> {$hours}h {$minutes}m\n";
        }
        
        if ($attendance->shift) {
            $message .= "🔄 <b>Shift:</b> {$attendance->shift->name}\n";
            $requiredHours = $attendance->shift->minimum_work_hours;
            $isComplete = $attendance->work_hours >= $requiredHours;
            $statusEmoji = $isComplete ? '✅' : '⚠️';
            $message .= "{$statusEmoji} <b>Required Hours:</b> {$requiredHours}h\n";
        }
        
        if ($attendance->notes) {
            $message .= "\n💬 <b>Notes:</b> {$attendance->notes}\n";
        }

        return $message;
    }

    /**
     * Send custom message
     */
    public function sendMessage($chatId, $message, $parseMode = 'HTML')
    {
        if (!$this->telegram) {
            return false;
        }

        try {
            $this->telegram->sendMessage($chatId, $message, $parseMode);
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Message Error: ' . $e->getMessage());
            return false;
        }
    }
}
