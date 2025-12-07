<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;
    
    protected function afterCreate(): void
    {
        $stockAdjustment = $this->record;
        
        // If status is APPROVED, update stock and create movement
        if ($stockAdjustment->status === 'APPROVED') {
            $this->processStockAdjustment($stockAdjustment);
        }
    }
    
    protected function processStockAdjustment($stockAdjustment): void
    {
        // Update stock record
        $stock = Stock::where([
            'item_id' => $stockAdjustment->item_id,
            'warehouse_id' => $stockAdjustment->warehouse_id,
            'location_id' => $stockAdjustment->location_id,
            'batch_number' => $stockAdjustment->batch_number,
        ])->first();
        
        if ($stock) {
            $stock->quantity += $stockAdjustment->quantity; // Can be positive or negative
            $stock->last_updated = now();
            $stock->save();
            
            // Delete if quantity reaches 0 or below
            if ($stock->quantity <= 0) {
                $stock->delete();
            }
        } elseif ($stockAdjustment->quantity > 0) {
            // Create new stock record for positive adjustments (FOUND/CORRECTION)
            Stock::create([
                'item_id' => $stockAdjustment->item_id,
                'warehouse_id' => $stockAdjustment->warehouse_id,
                'location_id' => $stockAdjustment->location_id,
                'batch_number' => $stockAdjustment->batch_number,
                'quantity' => $stockAdjustment->quantity,
                'last_updated' => now(),
            ]);
        }
        
        // Create stock movement record
        StockMovement::create([
            'item_id' => $stockAdjustment->item_id,
            'from_warehouse_id' => $stockAdjustment->quantity < 0 ? $stockAdjustment->warehouse_id : null,
            'to_warehouse_id' => $stockAdjustment->quantity > 0 ? $stockAdjustment->warehouse_id : null,
            'from_location_id' => $stockAdjustment->quantity < 0 ? $stockAdjustment->location_id : null,
            'to_location_id' => $stockAdjustment->quantity > 0 ? $stockAdjustment->location_id : null,
            'movement_type' => 'ADJUST',
            'quantity' => abs($stockAdjustment->quantity),
            'reference_no' => $stockAdjustment->reference_no,
            'batch_number' => $stockAdjustment->batch_number,
            'notes' => "Adjustment ({$stockAdjustment->adjustment_type}): {$stockAdjustment->reason}",
            'user_id' => auth()->id(),
            'movement_date' => $stockAdjustment->adjustment_date,
        ]);
    }
}
