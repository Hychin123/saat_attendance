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
        $this->allPermissions = $allPermissions;
        
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
