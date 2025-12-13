<?php

namespace App\Observers;

use App\Models\SmithReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SmithReturnObserver
{
    /**
     * Handle the SmithReturn "updated" event.
     */
    public function updated(SmithReturn $smithReturn): void
    {
        // If status changed to completed, process the return
        if ($smithReturn->isDirty('status')) {
            $oldStatus = $smithReturn->getOriginal('status');
            $newStatus = $smithReturn->status;

            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $this->processReturn($smithReturn);
                
                // Set processing info
                if (!$smithReturn->processed_by) {
                    $smithReturn->setAttribute('processed_by', auth()->id());
                    $smithReturn->setAttribute('processed_at', now());
                    $smithReturn->saveQuietly();
                }
            }
        }
    }

    /**
     * Process the return - add defective item back and deduct replacement
     */
    protected function processReturn(SmithReturn $smithReturn): void
    {
        DB::transaction(function () use ($smithReturn) {
            // Add defective item back to stock
            $defectiveStock = \App\Models\Stock::where('item_id', $smithReturn->item_id)
                ->where('warehouse_id', $smithReturn->warehouse_id)
                ->first();

            if ($defectiveStock) {
                $defectiveStock->increment('quantity', (float) $smithReturn->quantity);

                // Create stock movement for returned item
                StockMovement::create([
                    'item_id' => $smithReturn->item_id,
                    'warehouse_id' => $smithReturn->warehouse_id,
                    'location_id' => $defectiveStock->location_id,
                    'movement_type' => 'in',
                    'quantity' => $smithReturn->quantity,
                    'reference_type' => 'smith_return',
                    'reference_id' => $smithReturn->id,
                    'notes' => "Defective item returned by {$smithReturn->user->name}. Reason: {$smithReturn->return_reason}",
                ]);
            }

            // If there's a replacement item, deduct it from stock
            if ($smithReturn->replacement_item_id && $smithReturn->replacement_quantity) {
                $replacementStock = \App\Models\Stock::where('item_id', $smithReturn->replacement_item_id)
                    ->where('warehouse_id', $smithReturn->warehouse_id)
                    ->first();

                if (!$replacementStock) {
                    throw new \Exception("No stock found for replacement item in the warehouse.");
                }

                if ($replacementStock->quantity < $smithReturn->replacement_quantity) {
                    throw new \Exception("Insufficient stock for replacement. Available: {$replacementStock->quantity}, Required: {$smithReturn->replacement_quantity}");
                }

                $replacementStock->decrement('quantity', (float) $smithReturn->replacement_quantity);

                // Create stock movement for replacement item
                StockMovement::create([
                    'item_id' => $smithReturn->replacement_item_id,
                    'warehouse_id' => $smithReturn->warehouse_id,
                    'location_id' => $replacementStock->location_id,
                    'movement_type' => 'out',
                    'quantity' => $smithReturn->replacement_quantity,
                    'reference_type' => 'smith_replacement',
                    'reference_id' => $smithReturn->id,
                    'notes' => "Replacement item issued to {$smithReturn->user->name} for defective return",
                ]);
            }
        });
    }
}
