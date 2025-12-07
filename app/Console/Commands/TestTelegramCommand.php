<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\User;

class TestTelegramCommand extends Command
{
    protected $signature = 'telegram:test {userId?}';
    protected $description = 'Test Telegram notification for a user';

    public function handle()
    {
        $userId = $this->argument('userId') ?? 1;
        $user = User::with('role')->find($userId);
        
        if (!$user) {
            $this->error("User not found!");
            return 1;
        }
        
        $this->info("Testing Telegram for: {$user->name}");
        $this->info("Chat ID: " . ($user->telegram_chat_id ?? 'NOT SET'));
        $this->info("Notifications Enabled: " . ($user->telegram_notifications ? 'YES' : 'NO'));
        
        if (!$user->telegram_chat_id) {
            $this->error("User has no telegram_chat_id set!");
            return 1;
        }
        
        if (!$user->telegram_notifications) {
            $this->error("Telegram notifications are disabled for this user!");
            return 1;
        }
        
        $telegramService = app(TelegramService::class);
        
        $this->info("Sending test message...");
        $result = $telegramService->sendMessage(
            $user->telegram_chat_id,
            "🧪 <b>Test Message</b>\n\nThis is a test from SAAT Attendance System for {$user->name}",
            'HTML'
        );
        
        if ($result) {
            $this->info("✅ Message sent successfully!");
        } else {
            $this->error("❌ Failed to send message. Check logs.");
        }
        
        return 0;
    }
}
