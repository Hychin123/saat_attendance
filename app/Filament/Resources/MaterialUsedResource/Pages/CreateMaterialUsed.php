<?php

namespace App\Filament\Resources\MaterialUsedResource\Pages;

use App\Filament\Resources\MaterialUsedResource;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialUsed extends CreateRecord
{
    protected static string $resource = MaterialUsedResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }

    protected function beforeValidate(): void
    {
        // Get form data
        $data = $this->form->getRawState();
        
        // Validate stock availability before creating material used
        if (isset($data['item_id']) && isset($data['quantity']) && isset($data['warehouse_id'])) {
            $itemModel = \App\Models\Item::find($data['item_id']);
            
            if (!$itemModel) {
                throw new \Exception("Item not found.");
            }
            
            // Check if stock exists for this item in the warehouse
            $availableStock = Stock::where('item_id', $data['item_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->sum('quantity');
            
            if ($availableStock <= 0) {
                throw new \Exception("Item '{$itemModel->item_name}' is out of stock. Cannot create material used record.");
            }
            
            if ($availableStock < $data['quantity']) {
                throw new \Exception("Insufficient stock for item '{$itemModel->item_name}'. Available: {$availableStock}, Requested: {$data['quantity']}");
            }
        }
    }

    protected function afterCreate(): void
    {
        $materialUsed = $this->record;
        
        // If status is approved, the observer will handle stock reduction automatically
        // No additional processing needed here
    }
}
