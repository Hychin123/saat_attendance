<?php

namespace App\Observers;

use App\Models\Stock;
use App\Models\User;
use App\Notifications\LowStockNotification;

class StockObserver
{
    /**
     * Handle the Stock "created" event.
     */
    public function created(Stock $stock): void
    {
        $this->checkLowStock($stock);
    }

    /**
     * Handle the Stock "updated" event.
     */
    public function updated(Stock $stock): void
    {
        // Only check if quantity was changed
        if ($stock->wasChanged('quantity')) {
            $this->checkLowStock($stock);
        }
    }

    /**
     * Handle the Stock "deleted" event.
     */
    public function deleted(Stock $stock): void
    {
        $this->checkLowStock($stock);
    }

    /**
     * Check if item stock is low and notify admins.
     */
    protected function checkLowStock(Stock $stock): void
    {
        $item = $stock->item;
        
        if (!$item || !$item->is_active) {
            return;
        }

        // Calculate total stock for the item across all warehouses
        $totalStock = $item->stocks()->sum('quantity');
        
        // Check if stock is at or below reorder level
        if ($totalStock <= $item->reorder_level) {
            // Get all super admin users
            $admins = User::where('is_super_admin', true)->get();
            
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

    /**
     * Handle the Stock "restored" event.
     */
    public function restored(Stock $stock): void
    {
        $this->checkLowStock($stock);
    }

    /**
     * Handle the Stock "force deleted" event.
     */
    public function forceDeleted(Stock $stock): void
    {
        $this->checkLowStock($stock);
    }
}
