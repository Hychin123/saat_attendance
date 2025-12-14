<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStockAdjustment extends EditRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function beforeValidate(): void
    {
        // Get form data
        $data = $this->form->getRawState();
        $record = $this->getRecord();
        
        // Validate stock before changing status to APPROVED for negative adjustments
        if ($record->status !== 'APPROVED' && isset($data['status']) && $data['status'] === 'APPROVED') {
            $this->validateStockBeforeAdjustment($record);
        }
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldStatus = $record->status;
        $record = parent::handleRecordUpdate($record, $data);
        
        // If status changed from non-APPROVED to APPROVED, process adjustment
        if ($oldStatus !== 'APPROVED' && $record->status === 'APPROVED') {
            $this->processStockAdjustment($record);
        }
        
        return $record;
    }
    
    protected function validateStockBeforeAdjustment($stockAdjustment): void
    {
        // Only validate for negative adjustments
        if ($stockAdjustment->quantity < 0) {
            // Check if stock exists for this item in the warehouse
            $stock = Stock::where('item_id', $stockAdjustment->item_id)
                ->where('warehouse_id', $stockAdjustment->warehouse_id)
                ->where('location_id', $stockAdjustment->location_id)
                ->where('batch_number', $stockAdjustment->batch_number)
                ->first();
            
            $itemModel = \App\Models\Item::find($stockAdjustment->item_id);
            
            if (!$stock) {
                throw new \Exception("No stock found for item '{$itemModel->item_name}' in the selected warehouse/location with batch {$stockAdjustment->batch_number}. Cannot approve negative adjustment.");
            }
            
            if ($stock->quantity <= 0) {
                throw new \Exception("Item '{$itemModel->item_name}' is out of stock (0 available). Cannot approve negative adjustment.");
            }
            
            $adjustmentAmount = abs($stockAdjustment->quantity);
            if ($stock->quantity < $adjustmentAmount) {
                throw new \Exception("Insufficient stock for item '{$itemModel->item_name}' to adjust. Available: {$stock->quantity}, Adjustment: -{$adjustmentAmount}");
            }
        }
    }
    
    protected function processStockAdjustment($stockAdjustment): void
    {
        // Check if stock movement already exists
        $existingMovement = StockMovement::where([
            'reference_no' => $stockAdjustment->reference_no,
            'item_id' => $stockAdjustment->item_id,
            'movement_type' => 'ADJUST',
        ])->first();
        
        if ($existingMovement) {
            return; // Skip if already processed
        }
        
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
