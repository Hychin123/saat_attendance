<?php

namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditStockIn extends EditRecord
{
    protected static string $resource = StockInResource::class;

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
        
        // If status changed from non-RECEIVED to RECEIVED, process stock in
        if ($oldStatus !== 'RECEIVED' && $record->status === 'RECEIVED') {
            $this->processStockIn($record);
        }
        
        return $record;
    }
    
    protected function processStockIn($stockIn): void
    {
        foreach ($stockIn->items as $item) {
            // Check if stock movement already exists for this item
            $existingMovement = StockMovement::where([
                'reference_no' => $stockIn->reference_no,
                'item_id' => $item->item_id,
                'movement_type' => 'IN',
            ])->first();
            
            if ($existingMovement) {
                continue; // Skip if already processed
            }
            
            // Update or create stock record
            $stock = Stock::firstOrNew([
                'item_id' => $item->item_id,
                'warehouse_id' => $stockIn->warehouse_id,
                'location_id' => $item->location_id,
                'batch_number' => $item->batch_number,
            ]);
            
            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
            $stock->expiry_date = $item->expiry_date;
            $stock->last_updated = now();
            $stock->save();
            
            // Create stock movement record
            StockMovement::create([
                'item_id' => $item->item_id,
                'from_warehouse_id' => null,
                'to_warehouse_id' => $stockIn->warehouse_id,
                'from_location_id' => null,
                'to_location_id' => $item->location_id,
                'movement_type' => 'IN',
                'quantity' => $item->quantity,
                'reference_no' => $stockIn->reference_no,
                'batch_number' => $item->batch_number,
                'expiry_date' => $item->expiry_date,
                'notes' => "Stock in from supplier: {$stockIn->supplier->supplier_name}",
                'user_id' => auth()->id(),
                'movement_date' => $stockIn->received_date,
            ]);
        }
    }
}
