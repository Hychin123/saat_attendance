<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStockTransfer extends EditRecord
{
    protected static string $resource = StockTransferResource::class;

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
        
        // Validate stock before changing status to COMPLETED
        if ($record->status !== 'COMPLETED' && isset($data['status']) && $data['status'] === 'COMPLETED') {
            $this->validateStockBeforeTransfer($record);
        }
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldStatus = $record->status;
        $record = parent::handleRecordUpdate($record, $data);
        
        // If status changed from non-COMPLETED to COMPLETED, process transfer
        if ($oldStatus !== 'COMPLETED' && $record->status === 'COMPLETED') {
            $this->processStockTransfer($record);
        }
        
        return $record;
    }
    
    protected function validateStockBeforeTransfer($stockTransfer): void
    {
        foreach ($stockTransfer->items as $item) {
            // Check if stock exists for this item in the source warehouse
            $stock = Stock::where('item_id', $item->item_id)
                ->where('warehouse_id', $stockTransfer->from_warehouse_id)
                ->where('location_id', $item->from_location_id)
                ->where('batch_number', $item->batch_number)
                ->first();
            
            $itemModel = \App\Models\Item::find($item->item_id);
            
            if (!$stock) {
                throw new \Exception("No stock found for item '{$itemModel->item_name}' in the source warehouse/location with batch {$item->batch_number}. Please check stock availability.");
            }
            
            if ($stock->quantity <= 0) {
                throw new \Exception("Item '{$itemModel->item_name}' is out of stock (0 available) in the source warehouse/location. Cannot complete transfer.");
            }
            
            if ($stock->quantity < $item->quantity) {
                throw new \Exception("Insufficient stock for item '{$itemModel->item_name}' in source warehouse. Available: {$stock->quantity}, Requested: {$item->quantity}");
            }
        }
    }
    
    protected function processStockTransfer($stockTransfer): void
    {
        foreach ($stockTransfer->items as $item) {
            // Check if stock movement already exists
            $existingMovement = StockMovement::where([
                'reference_no' => $stockTransfer->reference_no,
                'item_id' => $item->item_id,
                'movement_type' => 'TRANSFER',
            ])->first();
            
            if ($existingMovement) {
                continue; // Skip if already processed
            }
            
            // Reduce stock from source warehouse
            $fromStock = Stock::where([
                'item_id' => $item->item_id,
                'warehouse_id' => $stockTransfer->from_warehouse_id,
                'location_id' => $item->from_location_id,
                'batch_number' => $item->batch_number,
            ])->first();
            
            if ($fromStock) {
                $fromStock->quantity -= $item->quantity;
                $fromStock->last_updated = now();
                $fromStock->save();
                
                if ($fromStock->quantity <= 0) {
                    $fromStock->delete();
                }
            }
            
            // Add stock to destination warehouse
            $toStock = Stock::firstOrNew([
                'item_id' => $item->item_id,
                'warehouse_id' => $stockTransfer->to_warehouse_id,
                'location_id' => $item->to_location_id,
                'batch_number' => $item->batch_number,
            ]);
            
            $toStock->quantity = ($toStock->quantity ?? 0) + $item->quantity;
            $toStock->expiry_date = $fromStock->expiry_date ?? null;
            $toStock->last_updated = now();
            $toStock->save();
            
            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => $stockTransfer->from_warehouse_id,
                'to_warehouse_id' => $stockTransfer->to_warehouse_id,
                'from_location_id' => $item->from_location_id,
                'to_location_id' => $item->to_location_id,
                'movement_type' => 'TRANSFER',
                'quantity' => $item->quantity,
                'reference_no' => $stockTransfer->reference_no,
                'batch_number' => $item->batch_number,
                'notes' => "Transfer from {$stockTransfer->fromWarehouse->warehouse_name} to {$stockTransfer->toWarehouse->warehouse_name}",
                'user_id' => auth()->id(),
                'movement_date' => $stockTransfer->transfer_date,
            ]);
        }
    }
}
