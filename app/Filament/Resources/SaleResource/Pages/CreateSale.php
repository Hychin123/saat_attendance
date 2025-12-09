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

        return $sale;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
