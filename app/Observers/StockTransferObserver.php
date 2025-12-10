<?php

namespace App\Observers;

use App\Models\StockTransfer;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockTransferObserver
{
    /**
     * Handle the StockTransfer "created" event.
     */
    public function created(StockTransfer $stockTransfer): void
    {
        // Process stock transfer when status is approved
        if ($stockTransfer->status === 'APPROVED') {
            $this->processStockTransfer($stockTransfer);
        }
    }

    /**
     * Handle the StockTransfer "updated" event.
     */
    public function updated(StockTransfer $stockTransfer): void
    {
        // Check if status changed to approved
        if ($stockTransfer->isDirty('status') && $stockTransfer->status === 'APPROVED') {
            $oldStatus = $stockTransfer->getOriginal('status');
            
            // Only process if status just changed to approved
            if ($oldStatus !== 'APPROVED') {
                $this->processStockTransfer($stockTransfer);
            }
        }
    }

    /**
     * Handle the StockTransfer "deleted" event.
     */
    public function deleted(StockTransfer $stockTransfer): void
    {
        // Reverse the stock transfer if it was approved
        if ($stockTransfer->status === 'APPROVED') {
            $this->reverseStockTransfer($stockTransfer);
        }
    }

    /**
     * Process stock transfer by reducing from source and adding to destination
     */
    protected function processStockTransfer(StockTransfer $stockTransfer): void
    {
        foreach ($stockTransfer->items as $item) {
            // Find source stock
            if ($item->from_location_id) {
                $fromStock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                    ->where('location_id', $item->from_location_id)
                    ->first();
            } else {
                $fromStock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                    ->first();
            }
            
            if (!$fromStock) {
                throw new \Exception("Source stock not found for item {$item->item_id} in warehouse {$stockTransfer->from_warehouse_id}");
            }
            
            if ($fromStock->quantity < $item->quantity) {
                throw new \Exception("Insufficient stock for transfer. Available: {$fromStock->quantity}, Required: {$item->quantity}");
            }
            
            // Reduce stock from source
            $fromStock->decrement('quantity', $item->quantity);
            $fromStock->update(['last_updated' => now()]);

            // Add stock to destination warehouse/location
            $toStock = Stock::where('item_id', $item->item_id)
                ->where('warehouse_id', $stockTransfer->to_warehouse_id)
                ->where('location_id', $item->to_location_id)
                ->first();

            if ($toStock) {
                $toStock->increment('quantity', $item->quantity);
                $toStock->update(['last_updated' => now()]);
            } else {
                // Create new stock record if it doesn't exist
                Stock::create([
                    'item_id' => $item->item_id,
                    'warehouse_id' => $stockTransfer->to_warehouse_id,
                    'location_id' => $item->to_location_id,
                    'quantity' => $item->quantity,
                    'batch_number' => $item->batch_number,
                    'last_updated' => now(),
                ]);
            }

            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => $stockTransfer->from_warehouse_id,
                'from_location_id' => $fromStock->location_id,
                'to_warehouse_id' => $stockTransfer->to_warehouse_id,
                'to_location_id' => $item->to_location_id,
                'quantity' => $item->quantity,
                'movement_type' => 'TRANSFER',
                'reference_no' => $stockTransfer->reference_no,
                'notes' => "Stock transferred: {$stockTransfer->reference_no}",
                'user_id' => $stockTransfer->approved_by ?? auth()->id() ?? 1,
                'movement_date' => now()->toDateString(),
            ]);
        }
    }

    /**
     * Reverse stock transfer by restoring to source and removing from destination
     */
    protected function reverseStockTransfer(StockTransfer $stockTransfer): void
    {
        foreach ($stockTransfer->items as $item) {
            // Find and restore source stock
            if ($item->from_location_id) {
                $fromStock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                    ->where('location_id', $item->from_location_id)
                    ->first();
            } else {
                $fromStock = \App\Models\Stock::where('item_id', $item->item_id)
                    ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                    ->first();
            }
            
            if ($fromStock) {
                $fromStock->increment('quantity', $item->quantity);
                $fromStock->update(['last_updated' => now()]);
            }

            // Find and reduce destination stock
            $toStock = \App\Models\Stock::where('item_id', $item->item_id)
                ->where('warehouse_id', $stockTransfer->to_warehouse_id)
                ->where('location_id', $item->to_location_id)
                ->first();
            
            if ($toStock) {
                $toStock->decrement('quantity', $item->quantity);
                $toStock->update(['last_updated' => now()]);
            }

            // Create stock movement record for reversal
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => $stockTransfer->to_warehouse_id,
                'from_location_id' => $item->to_location_id,
                'to_warehouse_id' => $stockTransfer->from_warehouse_id,
                'to_location_id' => $fromStock ? $fromStock->location_id : $item->from_location_id,
                'quantity' => $item->quantity,
                'movement_type' => 'TRANSFER',
                'reference_no' => $stockTransfer->reference_no,
                'notes' => "Stock transfer reversed: {$stockTransfer->reference_no}",
                'user_id' => auth()->id() ?? 1,
                'movement_date' => now()->toDateString(),
            ]);
        }
    }
}
