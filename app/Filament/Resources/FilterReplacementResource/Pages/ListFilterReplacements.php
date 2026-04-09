<?php

namespace App\Filament\Resources\FilterReplacementResource\Pages;

use App\Filament\Resources\FilterReplacementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilterReplacements extends ListRecords
{
    protected static string $resource = FilterReplacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
