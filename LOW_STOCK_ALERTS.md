# Low Stock Alert System

## Overview
The system automatically monitors inventory levels and notifies administrators when items reach or fall below their reorder levels.

## Features

### 1. Real-time Monitoring
- Automatic stock level checks after any stock changes
- Notifications triggered when stock ≤ reorder level
- Prevents duplicate notifications for the same item

### 2. Database Notifications
- Notifications appear in the Filament admin panel
- Bell icon in the navbar shows unread notification count
- Notifications poll every 30 seconds for updates
- Click on notification to view item details

### 3. Notification Details
Each notification includes:
- Item name and code
- Current stock level
- Reorder level
- Unit of measurement
- Direct link to edit the item

### 4. Scheduled Checks
- Automatic daily check at 9:00 AM
- Scans all active items for low stock
- Notifies all super admin users

## How It Works

### Automatic Triggers
Stock changes are monitored through the `StockObserver` which triggers on:
- New stock entry created
- Stock quantity updated
- Stock entry deleted
- Stock entry restored

### Manual Check
Run the command manually at any time:
```bash
php artisan stock:check-low
```

### Who Gets Notified
Only users with `is_super_admin = true` receive low stock notifications.

## Configuration

### Reorder Level
Set the reorder level for each item in the Item resource:
1. Navigate to Items
2. Edit an item
3. Set the "Reorder Level" field
4. Save

### Notification Polling
Adjust polling frequency in `AdminPanelProvider.php`:
```php
->databaseNotificationsPolling('30s')  // Change as needed
```

### Schedule Time
Modify the schedule in `routes/console.php`:
```php
Schedule::command('stock:check-low')->dailyAt('09:00');
```

## Testing

### Test the Command
```bash
php artisan stock:check-low
```

### View Notifications
1. Log in as an admin user
2. Look for the bell icon in the top navbar
3. Click to see all notifications
4. Click "View Item" to go to the item details

## Troubleshooting

### Notifications Not Appearing
1. Ensure you're logged in as a super admin user
2. Clear cache: `php artisan optimize:clear`
3. Check database notifications table exists
4. Verify stock levels are at or below reorder level

### Manual Database Check
```sql
SELECT * FROM notifications WHERE type = 'App\\Notifications\\LowStockNotification';
```

## Related Files
- `app/Notifications/LowStockNotification.php` - Notification class
- `app/Observers/StockObserver.php` - Stock monitoring
- `app/Console/Commands/CheckLowStockCommand.php` - Manual check command
- `routes/console.php` - Schedule configuration
- `app/Providers/AppServiceProvider.php` - Observer registration
- `app/Providers/Filament/AdminPanelProvider.php` - Filament configuration
