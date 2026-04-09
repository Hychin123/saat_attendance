<?php

namespace App\Filament\Resources\MaterialUsedResource\Pages;

use App\Filament\Resources\MaterialUsedResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialUsed extends ViewRecord
{
    protected static string $resource = MaterialUsedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
