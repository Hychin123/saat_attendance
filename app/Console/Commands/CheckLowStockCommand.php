<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-low';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock items and notify admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for low stock items...');
        
        // Get all active items
        $items = Item::where('is_active', true)
            ->with('stocks')
            ->get();
        
        $lowStockCount = 0;
        $admins = User::where('is_super_admin', true)->get();
        
        if ($admins->isEmpty()) {
            $this->warn('No admin users found to notify.');
            return Command::SUCCESS;
        }
        
        foreach ($items as $item) {
            $totalStock = $item->stocks()->sum('quantity');
            
            // Check if stock is at or below reorder level
            if ($totalStock <= $item->reorder_level) {
                $lowStockCount++;
                
                $this->warn("Low stock: {$item->item_name} ({$item->item_code}) - Current: {$totalStock}, Reorder Level: {$item->reorder_level}");
                
                // Notify each admin
                foreach ($admins as $admin) {
                    // Check if admin already has unread notification for this item
                    $hasUnreadNotification = $admin->unreadNotifications()
                        ->where('type', LowStockNotification::class)
                        ->whereJsonContains('data->item_id', $item->id)
                        ->exists();
                    
                    // Only send if no unread notification exists for this item
                    if (!$hasUnreadNotification) {
                        $admin->notify(new LowStockNotification($item, $totalStock));
                    }
                }
            }
        }
        
        if ($lowStockCount > 0) {
            $this->info("Found {$lowStockCount} low stock item(s). Notifications sent to " . $admins->count() . " admin(s).");
        } else {
            $this->info('No low stock items found.');
        }
        
        return Command::SUCCESS;
    }
}
