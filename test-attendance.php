<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Attendance;
use App\Models\Shift;

echo "🧪 Testing Attendance Notification System\n";
echo str_repeat("=", 50) . "\n\n";

// Find a user with Telegram enabled
$user = User::where('telegram_notifications', true)
    ->whereNotNull('telegram_chat_id')
    ->first();

if (!$user) {
    echo "❌ No users found with Telegram notifications enabled\n";
    echo "Please configure a user with Telegram settings first.\n";
    exit(1);
}

echo "👤 User: {$user->name}\n";
echo "📱 Chat ID: {$user->telegram_chat_id}\n";
echo "✅ Notifications: Enabled\n\n";

// Get a shift
$shift = Shift::first();
if ($shift) {
    echo "🔄 Shift: {$shift->name}\n";
    echo "⏰ Shift Time: {$shift->start_time}\n\n";
}

echo "Creating test attendance...\n";

try {
    // Create attendance (this should trigger the Observer)
    $attendance = new Attendance([
        'user_id' => $user->id,
        'shift_id' => $shift ? $shift->id : null,
        'date' => now()->toDateString(),
        'time_in' => now(),
        'is_late' => false,
        'late_minutes' => 0,
    ]);
    
    $attendance->save();
    
    echo "✅ Attendance created successfully!\n";
    echo "📝 Attendance ID: {$attendance->id}\n\n";
    
    echo "🔍 Check your Telegram channel for the notification!\n";
    echo "Channel ID: " . config('services.telegram.channel_id') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
