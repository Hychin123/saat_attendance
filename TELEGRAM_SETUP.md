# Telegram Bot Integration - Setup Guide

## 📱 How to Set Up Telegram Notifications

### 1️⃣ Create a Telegram Bot

1. Open Telegram and search for **@BotFather**
2. Start a chat and send `/newbot`
3. Follow the instructions to create your bot
4. You'll receive a **Bot Token** - save this!

Example Token: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`

### 2️⃣ Get Your Chat ID

**For Personal Notifications:**
1. Search for **@userinfobot** on Telegram
2. Start a chat and it will show your **Chat ID**
3. Save this Chat ID for each user

**For Channel/Group Notifications:**
1. Add your bot to the channel/group as admin
2. Send a message in the channel/group
3. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
4. Look for `"chat":{"id":-1001234567890` - this is your Channel ID

### 3️⃣ Configure Your Application

1. Open `.env` file
2. Add your bot token and channel ID:

```env
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHANNEL_ID=-1001234567890
```

3. Save the file

### 4️⃣ Configure User Settings

1. Go to **Users** in Filament Admin
2. Edit a user
3. Expand **Telegram Notifications** section
4. Enter the user's **Telegram Chat ID**
5. Enable **Telegram Notifications** toggle
6. Save

## 🔔 How It Works

### Check-In Notification
When a user scans attendance (check-in), they receive:
- ✅ Employee name and role
- 📅 Date and time
- 🔄 Assigned shift
- ⚠️ Late status (if applicable)
- ⏱️ Late minutes

### Check-Out Notification
When a user checks out, they receive:
- 🏁 Check-out confirmation
- ⏰ Time in and time out
- ⌛ Total work hours
- ✅ Completion status (met minimum hours)

## 📝 Features

- **Personal Notifications**: Each user receives their own attendance alerts
- **Channel Broadcasting**: All attendance is posted to configured channel
- **Late Detection**: Automatically calculates if user is late based on shift
- **Work Hours Tracking**: Shows total hours worked
- **Shift Integration**: Displays shift information and requirements
- **Rich Formatting**: Beautiful HTML-formatted messages with emojis

## 🧪 Testing

1. Make sure your bot token and chat ID are configured
2. Create/edit a user and add their Telegram Chat ID
3. Enable Telegram notifications for that user
4. Check-in through the attendance system
5. User should receive a Telegram notification instantly!

## 🔧 Troubleshooting

**Not receiving notifications?**
1. Check if `TELEGRAM_BOT_TOKEN` is set in `.env`
2. Verify user's `telegram_chat_id` is correct
3. Ensure `telegram_notifications` is enabled for the user
4. Check if bot is blocked by the user
5. Review Laravel logs: `storage/logs/laravel.log`

**How to verify bot is working?**
Send a test message:
```
https://api.telegram.org/bot<YOUR_BOT_TOKEN>/sendMessage?chat_id=<CHAT_ID>&text=Test
```

## 💡 Tips

- Users can get their Chat ID from @userinfobot
- Channel IDs start with `-100` (e.g., `-1001234567890`)
- Bot must be admin in channels to send messages
- Keep bot token secret - never commit to git!
