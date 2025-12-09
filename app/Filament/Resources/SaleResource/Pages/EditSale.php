<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\SaleItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load sale items
        $sale = $this->getRecord();
        $data['items'] = $sale->items->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'notes' => $item->notes,
            ];
        })->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate totals
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Store warehouse_id and items
        $warehouseId = $data['warehouse_id'];
        $items = $data['items'] ?? [];
        
        // Remove items from data to update sale record
        unset($data['items']);

        // Update the sale record
        $record->update($data);

        // Delete existing items and recreate
        $record->items()->delete();

        // Create sale items with warehouse_id
        foreach ($items as $item) {
            SaleItem::create([
                'sale_id' => $record->sale_id,
                'item_id' => $item['item_id'],
                'warehouse_id' => $warehouseId,
                'location_id' => $item['location_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0),
                'notes' => $item['notes'] ?? null,
            ]);
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
