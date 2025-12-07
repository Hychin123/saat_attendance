<?php

namespace App\Filament\Resources\StockOutResource\Pages;

use App\Filament\Resources\StockOutResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStockOut extends EditRecord
{
    protected static string $resource = StockOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldStatus = $record->status;
        $record = parent::handleRecordUpdate($record, $data);
        
        // If status changed from non-DISPATCHED to DISPATCHED, process stock out
        if ($oldStatus !== 'DISPATCHED' && $record->status === 'DISPATCHED') {
            $this->processStockOut($record);
        }
        
        return $record;
    }
    
    protected function processStockOut($stockOut): void
    {
        foreach ($stockOut->items as $item) {
            // Check if stock movement already exists for this item
            $existingMovement = StockMovement::where([
                'reference_no' => $stockOut->reference_no,
                'item_id' => $item->item_id,
                'movement_type' => 'OUT',
            ])->first();
            
            if ($existingMovement) {
                continue; // Skip if already processed
            }
            
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
