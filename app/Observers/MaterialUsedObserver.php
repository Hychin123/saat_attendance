<?php

namespace App\Observers;

use App\Models\MaterialUsed;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class MaterialUsedObserver
{
    /**
     * Handle the MaterialUsed "created" event.
     */
    public function created(MaterialUsed $materialUsed): void
    {
        // Reduce stock when material is used (if approved or auto-approve)
        if ($materialUsed->status === 'approved') {
            $this->reduceStock($materialUsed);
        }
    }

    /**
     * Handle the MaterialUsed "updated" event.
     */
    public function updated(MaterialUsed $materialUsed): void
    {
        // If status changed to approved, reduce stock
        if ($materialUsed->isDirty('status')) {
            $oldStatus = $materialUsed->getOriginal('status');
            $newStatus = $materialUsed->status;

            if ($newStatus === 'approved' && $oldStatus !== 'approved') {
                $this->reduceStock($materialUsed);
                
                // Set approval info
                if (!$materialUsed->approved_by) {
                    $materialUsed->setAttribute('approved_by', auth()->id());
                    $materialUsed->setAttribute('approved_at', now());
                    $materialUsed->saveQuietly();
                }
            }
        }
    }

    /**
     * Handle the MaterialUsed "deleted" event.
     */
    public function deleted(MaterialUsed $materialUsed): void
    {
        // Restore stock if material usage was approved
        if ($materialUsed->status === 'approved') {
            $this->restoreStock($materialUsed);
        }
    }

    /**
     * Reduce stock when material is used
     */
    protected function reduceStock(MaterialUsed $materialUsed): void
    {
        DB::transaction(function () use ($materialUsed) {
            // Find or create stock record
            $stock = \App\Models\Stock::where('item_id', $materialUsed->item_id)
                ->where('warehouse_id', $materialUsed->warehouse_id)
                ->first();

            if (!$stock) {
                throw new \Exception("No stock found for this item in the warehouse.");
            }

            if ($stock->quantity < $materialUsed->quantity) {
                throw new \Exception("Insufficient stock. Available: {$stock->quantity}, Required: {$materialUsed->quantity}");
            }

            // Reduce stock
            $stock->decrement('quantity', (float) $materialUsed->quantity);

            // Create stock movement record
            StockMovement::create([
                'item_id' => $materialUsed->item_id,
                'from_warehouse_id' => $materialUsed->warehouse_id,
                'from_location_id' => $stock->location_id,
                'movement_type' => 'OUT',
                'quantity' => $materialUsed->quantity,
                'reference_no' => $materialUsed->reference_no,
                'notes' => "Material used by smith: {$materialUsed->user->name}. Project: {$materialUsed->project_name}",
                'user_id' => auth()->id() ?? $materialUsed->user_id,
                'movement_date' => $materialUsed->usage_date,
            ]);
        });
    }

    /**
     * Restore stock when material usage is cancelled/deleted
     */
    protected function restoreStock(MaterialUsed $materialUsed): void
    {
        DB::transaction(function () use ($materialUsed) {
            // Find stock record
            $stock = \App\Models\Stock::where('item_id', $materialUsed->item_id)
                ->where('warehouse_id', $materialUsed->warehouse_id)
                ->first();

            if ($stock) {
                // Add stock back
                $stock->increment('quantity', (float) $materialUsed->quantity);

                // Create stock movement record
                StockMovement::create([
                    'item_id' => $materialUsed->item_id,
                    'from_warehouse_id' => $materialUsed->warehouse_id,
                    'from_location_id' => $stock->location_id,
                    'movement_type' => 'IN',
                    'quantity' => $materialUsed->quantity,
                    'reference_no' => $materialUsed->reference_no,
                    'notes' => "Material usage cancelled/deleted - stock restored",
                    'user_id' => auth()->id() ?? $materialUsed->user_id,
                    'movement_date' => now(),
                ]);
            }
        });
    }
}
