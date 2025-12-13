<?php

namespace App\Filament\Resources\MaterialUsedResource\Pages;

use App\Filament\Resources\MaterialUsedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialUsed extends EditRecord
{
    protected static string $resource = MaterialUsedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
