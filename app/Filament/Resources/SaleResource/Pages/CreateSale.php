<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function beforeValidate(): void
    {
        // Get form data
        $data = $this->form->getRawState();
        
        // Validate stock availability before creating sale
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
                
                // Check if stock exists for this item in the warehouse
                $stock = \App\Models\Stock::where('item_id', $item['item_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                
                if (!$stock) {
                    throw new \Exception("No stock found for item '{$itemModel->item_name}' in the selected warehouse. Please add stock first.");
                }
                
                if ($stock->quantity <= 0) {
                    throw new \Exception("Item '{$itemModel->item_name}' is out of stock (0 available). Please restock before creating a sale.");
                }
                
                if ($stock->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for item '{$itemModel->item_name}'. Available: {$stock->quantity}, Requested: {$item['quantity']}");
                }
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate totals
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $totalAmount += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            }
        }

        $data['total_amount'] = $totalAmount;
        $data['net_total'] = $totalAmount - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);
        $data['remaining_amount'] = $data['net_total'] - ($data['deposit_amount'] ?? 0);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Store warehouse_id and items
        $warehouseId = $data['warehouse_id'];
        $items = $data['items'] ?? [];
        
        // Remove items from data to create sale record
        unset($data['items']);

        // Create the sale record
        $sale = static::getModel()::create($data);

        // Create sale items with warehouse_id
        foreach ($items as $item) {
            SaleItem::create([
                'sale_id' => $sale->sale_id,
                'item_id' => $item['item_id'],
                'warehouse_id' => $warehouseId,
                'location_id' => $item['location_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0),
                'notes' => $item['notes'] ?? null,
            ]);
        }

        // Refresh the sale to load the items, then trigger stock reduction
        $sale->refresh();
        $sale->load('items');
        
        // Manually trigger stock reduction now that items are loaded
        app(\App\Observers\SaleObserver::class)->reduceStockAfterCreation($sale);

        return $sale;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
