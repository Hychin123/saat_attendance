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
    
    protected function beforeValidate(): void
    {
        // Get form data
        $data = $this->form->getRawState();
        
        // Validate stock availability for negative adjustments
        if (isset($data['quantity']) && $data['quantity'] < 0) {
            $itemId = $data['item_id'] ?? null;
            $warehouseId = $data['warehouse_id'] ?? null;
            $locationId = $data['location_id'] ?? null;
            $batchNumber = $data['batch_number'] ?? null;
            
            if (!$itemId) {
                throw new \Exception("Please select an item first.");
            }
            
            if (!$warehouseId) {
                throw new \Exception("Please select a warehouse first.");
            }
            
            $itemModel = \App\Models\Item::find($itemId);
            
            if (!$itemModel) {
                throw new \Exception("Item not found.");
            }
            
            // Check if stock exists for this item in the warehouse
            $stockQuery = Stock::where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId);
                
            if ($locationId) {
                $stockQuery->where('location_id', $locationId);
            }
            
            if ($batchNumber) {
                $stockQuery->where('batch_number', $batchNumber);
            }
            
            $stock = $stockQuery->first();
            
            if (!$stock) {
                $batchInfo = $batchNumber ? " with batch {$batchNumber}" : "";
                throw new \Exception("No stock found for item '{$itemModel->item_name}' in the selected warehouse/location{$batchInfo}. Cannot create negative adjustment.");
            }
            
            if ($stock->quantity <= 0) {
                throw new \Exception("Item '{$itemModel->item_name}' is out of stock (0 available). Cannot create negative adjustment.");
            }
            
            $adjustmentAmount = abs($data['quantity']);
            if ($stock->quantity < $adjustmentAmount) {
                throw new \Exception("Insufficient stock for item '{$itemModel->item_name}' to adjust. Available: {$stock->quantity}, Adjustment: -{$adjustmentAmount}");
            }
        }
    }
    
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
