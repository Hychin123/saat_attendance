<?php

namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockIn extends CreateRecord
{
    protected static string $resource = StockInResource::class;
    
    protected function afterCreate(): void
    {
        $stockIn = $this->record;
        
        // If status is RECEIVED, update stock and create movements
        if ($stockIn->status === 'RECEIVED') {
            $this->processStockIn($stockIn);
        }
    }
    
    protected function processStockIn($stockIn): void
    {
        foreach ($stockIn->items as $item) {
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

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
