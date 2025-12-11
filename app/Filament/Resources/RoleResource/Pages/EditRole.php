<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load permissions for each resource
        $resources = ['users', 'roles', 'permissions', 'attendances', 'shifts', 
                      'warehouses', 'items', 'categories', 'brands', 'suppliers', 'locations',
                      'stocks', 'stock_ins', 'stock_outs', 'stock_transfers', 'stock_adjustments', 'stock_movements',
                      'sales', 'payments', 'commissions'];
        
        foreach ($resources as $resource) {
            $data["permissions_{$resource}"] = $this->record->permissions()
                ->where('resource', $resource)
                ->pluck('permissions.id')
                ->toArray();
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract all permission fields and merge them
        $allPermissions = [];
        
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_')) {
                if (is_array($value)) {
                    $allPermissions = array_merge($allPermissions, $value);
                }
                unset($data[$key]);
            }
        }
        
        // Store permissions temporarily
        $this->allPermissions = array_unique($allPermissions);
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Sync all permissions
        if (isset($this->allPermissions)) {
            $this->record->permissions()->sync($this->allPermissions);
        }
    }
}
