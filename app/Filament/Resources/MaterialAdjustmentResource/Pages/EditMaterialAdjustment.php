<?php

namespace App\Filament\Resources\MaterialAdjustmentResource\Pages;

use App\Filament\Resources\MaterialAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialAdjustment extends EditRecord
{
    protected static string $resource = MaterialAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
