<?php

namespace App\Filament\Resources\StockOutResource\Pages;

use App\Filament\Resources\StockOutResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockOut extends CreateRecord
{
    protected static string $resource = StockOutResource::class;
    
    protected function beforeValidate(): void
    {
        // Get form data
        $data = $this->form->getRawState();
        
        // Validate stock availability before creating stock out
        if (isset($data['items']) && is_array($data['items'])) {
            $warehouseId = $data['warehouse_id'] ?? null;
            
            if (!$warehouseId) {
                throw new \Exception("Please select a warehouse first.");
            }
            
            foreach ($data['items'] as $item) {
                if (!isset($item['item_id']) || !isset($item['quantity'])) {
                    continue;
                }
                
                $itemModel = \App\Models\Item::find($item['item_id']);
                
                if (!$itemModel) {
                    continue;
                }
                
                $locationId = $item['location_id'] ?? null;
                $batchNumber = $item['batch_number'] ?? null;
                
                // Check if stock exists for this item in the warehouse
                $stockQuery = Stock::where('item_id', $item['item_id'])
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
                    throw new \Exception("No stock found for item '{$itemModel->item_name}' in the selected warehouse/location{$batchInfo}. Please check stock availability.");
                }
                
                if ($stock->quantity <= 0) {
                    throw new \Exception("Item '{$itemModel->item_name}' is out of stock (0 available). Cannot create stock out.");
                }
                
                if ($stock->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for item '{$itemModel->item_name}'. Available: {$stock->quantity}, Requested: {$item['quantity']}");
                }
            }
        }
    }
    
    protected function afterCreate(): void
    {
        $stockOut = $this->record;
        
        // If status is DISPATCHED, update stock and create movements
        if ($stockOut->status === 'DISPATCHED') {
            $this->processStockOut($stockOut);
        }
    }
    
    protected function processStockOut($stockOut): void
    {
        foreach ($stockOut->items as $item) {
            // Update stock record
            $stock = Stock::where([
                'item_id' => $item->item_id,
                'warehouse_id' => $stockOut->warehouse_id,
                'location_id' => $item->location_id,
                'batch_number' => $item->batch_number,
            ])->first();
            
            if ($stock) {
                $stock->quantity -= $item->quantity;
                $stock->last_updated = now();
                $stock->save();
                
                // Delete if quantity reaches 0
                if ($stock->quantity <= 0) {
                    $stock->delete();
                }
            }
            
            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => $stockOut->warehouse_id,
                'to_warehouse_id' => null,
                'from_location_id' => $item->location_id,
                'to_location_id' => null,
                'movement_type' => 'OUT',
                'quantity' => $item->quantity,
                'reference_no' => $stockOut->reference_no,
                'batch_number' => $item->batch_number,
                'notes' => "Stock out to: {$stockOut->customer_department}. Reason: {$stockOut->reason}",
                'user_id' => auth()->id(),
                'movement_date' => $stockOut->dispatch_date,
            ]);
        }
    }
}
