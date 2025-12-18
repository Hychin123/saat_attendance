<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Commission;
use App\Models\StockMovement;
use App\Models\Machine;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        // Stock reduction is now handled manually after items are created
        // See SaleResource CreateSale page
        
        // If sale is created with COMPLETED status, create machines
        if ($sale->status === Sale::STATUS_COMPLETED) {
            $this->createMachinesForSale($sale);
            $this->generateCommission($sale);
        }
    }

    /**
     * Reduce stock after sale and items are created
     * This is called manually from CreateSale page
     */
    public function reduceStockAfterCreation(Sale $sale): void
    {
        $this->reduceStock($sale);
        
        // Also create machines if sale is completed
        if ($sale->status === Sale::STATUS_COMPLETED) {
            $this->createMachinesForSale($sale);
        }
    }

    /**
     * Handle the Sale "updated" event.
     * Main business logic for status changes
     */
    public function updated(Sale $sale): void
    {
        // Check if status changed
        if ($sale->isDirty('status')) {
            $oldStatus = $sale->getOriginal('status');
            $newStatus = $sale->status;

            // Note: Stock is already reduced when sale is created
            // No need to reduce again when status changes to PROCESSING

            // COMPLETED: Generate commission when sale is completed
            if ($newStatus === Sale::STATUS_COMPLETED && $oldStatus !== Sale::STATUS_COMPLETED) {
                $this->generateCommission($sale);
                $this->createMachinesForSale($sale);
                
                // Set completed date if not set
                if (!$sale->completed_date) {
                    $sale->setAttribute('completed_date', now());
                    $sale->saveQuietly(); // Prevent infinite loop
                }
            }

            // CANCELLED or REFUNDED: Restore stock
            if (in_array($newStatus, [Sale::STATUS_CANCELLED, Sale::STATUS_REFUNDED])) {
                $this->restoreStock($sale);
            }
        }
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        // Restore stock when sale is deleted (unless it's already cancelled/refunded)
        if (!in_array($sale->status, [Sale::STATUS_CANCELLED, Sale::STATUS_REFUNDED])) {
            $this->restoreStock($sale);
        }
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        //
    }

    /**
     * Handle the Sale "force deleted" event.
     */
    public function forceDeleted(Sale $sale): void
    {
        //
    }

    /**
     * Reduce stock when sale status changes to PROCESSING
     */
    protected function reduceStock(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            // Find stock record - if location_id is specified use it, otherwise get first available stock
            if ($item->location_id) {
                $stock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->where('location_id', $item->location_id)
                    ->first();
            } else {
                // If no location specified, get the first available stock in the warehouse
                $stock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->first();
            }
            
            if (!$stock) {
                throw new \Exception("Stock not found for item {$item->item_id} in warehouse {$item->warehouse_id}");
            }
            
            // Check if enough stock available
            if ($stock->quantity < $item->quantity) {
                throw new \Exception("Insufficient stock for item {$item->item->item_name}. Available: {$stock->quantity}, Required: {$item->quantity}");
            }
            
            // Reduce stock quantity
            $stock->decrement('quantity', $item->quantity);
            $stock->update(['last_updated' => now()]);

            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => $item->warehouse_id,
                'from_location_id' => $stock->location_id,
                'to_warehouse_id' => null,
                'to_location_id' => null,
                'quantity' => $item->quantity,
                'movement_type' => 'OUT',
                'reference_no' => $sale->sale_id,
                'notes' => "Stock reduced for Sale {$sale->sale_id}",
                'user_id' => auth()->id() ?? 1,
                'movement_date' => now()->toDateString(),
            ]);
        }
    }

    /**
     * Restore stock when sale is cancelled or refunded
     */
    protected function restoreStock(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            // Find stock record - if location_id is specified use it, otherwise get first available stock
            if ($item->location_id) {
                $stock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->where('location_id', $item->location_id)
                    ->first();
            } else {
                // If no location specified, get the first available stock in the warehouse
                $stock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->first();
            }
            
            if (!$stock) {
                // If stock doesn't exist, we can't restore (this shouldn't happen normally)
                continue;
            }
            
            // Restore stock quantity
            $stock->increment('quantity', $item->quantity);
            $stock->update(['last_updated' => now()]);

            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => null,
                'from_location_id' => null,
                'to_warehouse_id' => $item->warehouse_id,
                'to_location_id' => $stock->location_id,
                'quantity' => $item->quantity,
                'movement_type' => 'IN',
                'reference_no' => $sale->sale_id,
                'notes' => "Stock restored from {$sale->status} Sale {$sale->sale_id}",
                'user_id' => auth()->id() ?? 1,
                'movement_date' => now()->toDateString(),
            ]);
        }
    }

    /**
     * Generate commission when sale is completed
     */
    protected function generateCommission(Sale $sale): void
    {
        // Only generate commission if agent is assigned
        if (!$sale->agent_id) {
            return;
        }

        // Check if commission already exists
        $existingCommission = Commission::where('sale_id', $sale->sale_id)->first();
        
        if (!$existingCommission) {
            Commission::create([
                'sale_id' => $sale->sale_id,
                'agent_id' => $sale->agent_id,
                'commission_rate' => 5.00, // 5% commission
                'total_sale_amount' => $sale->net_total,
                'commission_amount' => $sale->net_total * 0.05,
                'status' => Commission::STATUS_PENDING,
            ]);
        }
    }

    /**
     * Create machine records for water vending machines in the sale
     */
    protected function createMachinesForSale(Sale $sale): void
    {
        // Check if machines already created
        if ($sale->machines()->count() > 0) {
            return;
        }

        // Ensure items are loaded
        if (!$sale->relationLoaded('items')) {
            $sale->load('items.item.category');
        }

        $saleItems = $sale->items;

        foreach ($saleItems as $saleItem) {
            $item = $saleItem->item;
            
            if (!$item) {
                continue; // Skip if item not found
            }
            
            // Check if item is a water vending machine
            if ($this->isWaterVendingMachine($item)) {
                // Create a machine for each quantity
                for ($i = 0; $i < $saleItem->quantity; $i++) {
                    Machine::create([
                        'sale_id' => $sale->sale_id,
                        'customer_id' => $sale->customer_id,
                        'model' => $item->item_name,
                        'install_date' => $sale->completed_date ?? now(),
                        'status' => Machine::STATUS_ACTIVE,
                        'notes' => "Created from Sale {$sale->sale_id}",
                    ]);
                }
            }
        }
    }

    /**
     * Determine if an item is a water vending machine
     * Customize this logic based on your needs
     */
    protected function isWaterVendingMachine(Item $item): bool
    {
        // Option 1: Check by category name
        if ($item->category && stripos($item->category->name, 'vending') !== false) {
            return true;
        }

        // Option 2: Check by item name
        if (stripos($item->item_name, 'vending') !== false || 
            stripos($item->item_name, 'water machine') !== false) {
            return true;
        }

        // Option 3: You can add a specific field in items table like 'is_machine'
        // if (isset($item->is_machine) && $item->is_machine) {
        //     return true;
        // }

        return false;
    }
}
