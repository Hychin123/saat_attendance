<?php

namespace App\Observers;

use App\Models\StockAdjustment;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockAdjustmentObserver
{
    /**
     * Handle the StockAdjustment "created" event.
     */
    public function created(StockAdjustment $stockAdjustment): void
    {
        // Process stock adjustment when status is approved
        if ($stockAdjustment->status === 'approved') {
            $this->processStockAdjustment($stockAdjustment);
        }
    }

    /**
     * Handle the StockAdjustment "updated" event.
     */
    public function updated(StockAdjustment $stockAdjustment): void
    {
        // Check if status changed to approved
        if ($stockAdjustment->isDirty('status') && $stockAdjustment->status === 'approved') {
            $oldStatus = $stockAdjustment->getOriginal('status');
            
            // Only process if status just changed to approved
            if ($oldStatus !== 'approved') {
                $this->processStockAdjustment($stockAdjustment);
            }
        }
    }

    /**
     * Handle the StockAdjustment "deleted" event.
     */
    public function deleted(StockAdjustment $stockAdjustment): void
    {
        // Reverse the stock adjustment if it was approved
        if ($stockAdjustment->status === 'approved') {
            $this->reverseStockAdjustment($stockAdjustment);
        }
    }

    /**
     * Process stock adjustment by adding or subtracting from stock
     */
    protected function processStockAdjustment(StockAdjustment $stockAdjustment): void
    {
        // Find stock record
        if ($stockAdjustment->location_id) {
            $stock = \App\Models\Stock::where('item_id', $stockAdjustment->item_id)
                ->where('warehouse_id', $stockAdjustment->warehouse_id)
                ->where('location_id', $stockAdjustment->location_id)
                ->first();
        } else {
            // If no location specified, get the first available stock in the warehouse
            $stock = \App\Models\Stock::where('item_id', $stockAdjustment->item_id)
                ->where('warehouse_id', $stockAdjustment->warehouse_id)
                ->first();
        }

        if (!$stock) {
            throw new \Exception("Stock not found for item {$stockAdjustment->item_id} in warehouse {$stockAdjustment->warehouse_id}");
        }

        // Apply adjustment based on type
        if ($stockAdjustment->adjustment_type === 'increase') {
            $stock->increment('quantity', $stockAdjustment->quantity);
            $movementType = 'IN';
        } else {
            if ($stock->quantity < $stockAdjustment->quantity) {
                throw new \Exception("Insufficient stock for adjustment. Available: {$stock->quantity}, Adjustment: {$stockAdjustment->quantity}");
            }
            $stock->decrement('quantity', $stockAdjustment->quantity);
            $movementType = 'OUT';
        }
        
        $stock->update(['last_updated' => now()]);

        // Create stock movement record
        StockMovement::create([
            'item_id' => $stockAdjustment->item_id,
            'from_warehouse_id' => $stockAdjustment->adjustment_type === 'decrease' ? $stockAdjustment->warehouse_id : null,
            'from_location_id' => $stockAdjustment->adjustment_type === 'decrease' ? $stock->location_id : null,
            'to_warehouse_id' => $stockAdjustment->adjustment_type === 'increase' ? $stockAdjustment->warehouse_id : null,
            'to_location_id' => $stockAdjustment->adjustment_type === 'increase' ? $stock->location_id : null,
            'quantity' => $stockAdjustment->quantity,
            'movement_type' => $movementType,
            'reference_no' => $stockAdjustment->reference_no,
            'notes' => "Stock adjustment ({$stockAdjustment->adjustment_type}): {$stockAdjustment->reason}",
            'user_id' => $stockAdjustment->approved_by ?? auth()->id() ?? 1,
            'movement_date' => now()->toDateString(),
        ]);
    }

    /**
     * Reverse stock adjustment
     */
    protected function reverseStockAdjustment(StockAdjustment $stockAdjustment): void
    {
        // Find stock record
        if ($stockAdjustment->location_id) {
            $stock = \App\Models\Stock::where('item_id', $stockAdjustment->item_id)
                ->where('warehouse_id', $stockAdjustment->warehouse_id)
                ->where('location_id', $stockAdjustment->location_id)
                ->first();
        } else {
            // If no location specified, get the first available stock in the warehouse
            $stock = \App\Models\Stock::where('item_id', $stockAdjustment->item_id)
                ->where('warehouse_id', $stockAdjustment->warehouse_id)
                ->first();
        }

        if (!$stock) {
            return; // Can't reverse if stock doesn't exist
        }

        // Reverse the adjustment
        if ($stockAdjustment->adjustment_type === 'increase') {
            $stock->decrement('quantity', $stockAdjustment->quantity);
            $movementType = 'OUT';
        } else {
            $stock->increment('quantity', $stockAdjustment->quantity);
            $movementType = 'IN';
        }
        
        $stock->update(['last_updated' => now()]);

        // Create stock movement record for reversal
        StockMovement::create([
            'item_id' => $stockAdjustment->item_id,
            'from_warehouse_id' => $stockAdjustment->adjustment_type === 'increase' ? $stockAdjustment->warehouse_id : null,
            'from_location_id' => $stockAdjustment->adjustment_type === 'increase' ? $stock->location_id : null,
            'to_warehouse_id' => $stockAdjustment->adjustment_type === 'decrease' ? $stockAdjustment->warehouse_id : null,
            'to_location_id' => $stockAdjustment->adjustment_type === 'decrease' ? $stock->location_id : null,
            'quantity' => $stockAdjustment->quantity,
            'movement_type' => $movementType,
            'reference_no' => $stockAdjustment->reference_no,
            'notes' => "Stock adjustment reversed: {$stockAdjustment->reference_no}",
            'user_id' => auth()->id() ?? 1,
            'movement_date' => now()->toDateString(),
        ]);
    }
}
