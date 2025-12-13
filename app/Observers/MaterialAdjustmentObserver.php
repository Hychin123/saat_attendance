<?php

namespace App\Observers;

use App\Models\MaterialAdjustment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class MaterialAdjustmentObserver
{
    /**
     * Handle the MaterialAdjustment "created" event.
     */
    public function created(MaterialAdjustment $materialAdjustment): void
    {
        // Apply adjustment when status is approved
        if ($materialAdjustment->status === 'approved') {
            $this->applyAdjustment($materialAdjustment);
        }
    }

    /**
     * Handle the MaterialAdjustment "updated" event.
     */
    public function updated(MaterialAdjustment $materialAdjustment): void
    {
        // If status changed to approved, apply adjustment
        if ($materialAdjustment->isDirty('status')) {
            $oldStatus = $materialAdjustment->getOriginal('status');
            $newStatus = $materialAdjustment->status;

            if ($newStatus === 'approved' && $oldStatus !== 'approved') {
                $this->applyAdjustment($materialAdjustment);
                
                // Set approval info
                if (!$materialAdjustment->approved_by) {
                    $materialAdjustment->setAttribute('approved_by', auth()->id());
                    $materialAdjustment->setAttribute('approved_at', now());
                    $materialAdjustment->saveQuietly();
                }
            }
        }
    }

    /**
     * Apply stock adjustment
     */
    protected function applyAdjustment(MaterialAdjustment $materialAdjustment): void
    {
        DB::transaction(function () use ($materialAdjustment) {
            // Find stock record
            $stock = \App\Models\Stock::where('item_id', $materialAdjustment->item_id)
                ->where('warehouse_id', $materialAdjustment->warehouse_id)
                ->first();

            if (!$stock) {
                throw new \Exception("No stock found for this item in the warehouse.");
            }

            // Apply adjustment
            if ($materialAdjustment->adjustment_type === 'add') {
                $stock->increment('quantity', (float) $materialAdjustment->quantity);
                $movementType = 'in';
            } else {
                if ($stock->quantity < $materialAdjustment->quantity) {
                    throw new \Exception("Insufficient stock. Available: {$stock->quantity}, Required: {$materialAdjustment->quantity}");
                }
                $stock->decrement('quantity', (float) $materialAdjustment->quantity);
                $movementType = 'out';
            }

            // Create stock movement record
            StockMovement::create([
                'item_id' => $materialAdjustment->item_id,
                'warehouse_id' => $materialAdjustment->warehouse_id,
                'location_id' => $stock->location_id,
                'movement_type' => $movementType,
                'quantity' => $materialAdjustment->quantity,
                'reference_type' => 'material_adjustment',
                'reference_id' => $materialAdjustment->id,
                'notes' => "Material adjustment ({$materialAdjustment->adjustment_type}) by {$materialAdjustment->user->name}. Reason: {$materialAdjustment->reason}",
            ]);
        });
    }
}
