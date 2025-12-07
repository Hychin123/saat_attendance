<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;
    
    protected function handleRecordCreation(array $data): Model
    {
        $stockTransfer = parent::handleRecordCreation($data);
        
        // If status is COMPLETED, update stock and create movements
        if ($stockTransfer->status === 'COMPLETED') {
            $this->processStockTransfer($stockTransfer);
        }
        
        return $stockTransfer;
    }
    
    protected function processStockTransfer($stockTransfer): void
    {
        foreach ($stockTransfer->items as $item) {
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
