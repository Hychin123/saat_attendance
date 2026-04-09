<?php

namespace App\Filament\Resources\MaterialAdjustmentResource\Pages;

use App\Filament\Resources\MaterialAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialAdjustment extends ViewRecord
{
    protected static string $resource = MaterialAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
