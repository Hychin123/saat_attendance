<?php

namespace App\Filament\Resources\MaterialUsedResource\Pages;

use App\Filament\Resources\MaterialUsedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaterialUseds extends ListRecords
{
    protected static string $resource = MaterialUsedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
