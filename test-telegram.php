<?php

require __DIR__.'/vendor/autoload.php';

use TelegramBot\Api\BotApi;

$botToken = '7971186532:AAH7wZAVxaq89c5Z2u68i-oN6x3N9LSOyos';
$channelId = '-1003127307254';

echo "Testing Telegram Bot...\n";
echo "Bot Token: " . substr($botToken, 0, 20) . "...\n";
echo "Channel ID: $channelId\n\n";

try {
    $telegram = new BotApi($botToken);
    
    $message = "🧪 <b>Test Message</b>\n\n";
    $message .= "Testing channel notifications\n";
    $message .= "Time: " . date('Y-m-d H:i:s');
    
    echo "Sending message to channel...\n";
    $result = $telegram->sendMessage($channelId, $message, 'HTML');
    
    echo "✅ Success! Message sent to channel!\n";
    echo "Message ID: " . $result->getMessageId() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
