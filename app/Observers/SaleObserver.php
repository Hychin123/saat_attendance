<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Commission;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        // Reduce stock immediately when sale is created
        $this->reduceStock($sale);
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
            // Build query to find stock
            $stockQuery = DB::table('stocks')
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->warehouse_id);
            
            // Add location_id condition if specified
            if ($item->location_id) {
                $stockQuery->where('location_id', $item->location_id);
            } else {
                $stockQuery->whereNull('location_id');
            }
            
            // Reduce stock quantity
            $stockQuery->decrement('quantity', $item->quantity);

            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => $item->warehouse_id,
                'from_location_id' => $item->location_id,
                'to_warehouse_id' => null,
                'to_location_id' => null,
                'quantity' => $item->quantity,
                'movement_type' => 'OUT',
                'reference_type' => 'SALE',
                'reference_no' => $sale->sale_id,
                'notes' => "Stock reduced for Sale {$sale->sale_id}",
                'moved_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Restore stock when sale is cancelled or refunded
     */
    protected function restoreStock(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            // Build query to find stock
            $stockQuery = DB::table('stocks')
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->warehouse_id);
            
            // Add location_id condition if specified
            if ($item->location_id) {
                $stockQuery->where('location_id', $item->location_id);
            } else {
                $stockQuery->whereNull('location_id');
            }
            
            // Restore stock quantity
            $stockQuery->increment('quantity', $item->quantity);

            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => null,
                'from_location_id' => null,
                'to_warehouse_id' => $item->warehouse_id,
                'to_location_id' => $item->location_id,
                'quantity' => $item->quantity,
                'movement_type' => 'IN',
                'reference_type' => 'SALE_RETURN',
                'reference_no' => $sale->sale_id,
                'notes' => "Stock restored from {$sale->status} Sale {$sale->sale_id}",
                'moved_by' => auth()->id(),
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
}
