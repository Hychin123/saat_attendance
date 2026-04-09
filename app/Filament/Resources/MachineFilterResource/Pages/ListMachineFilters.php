<?php

namespace App\Filament\Resources\MachineFilterResource\Pages;

use App\Filament\Resources\MachineFilterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachineFilters extends ListRecords
{
    protected static string $resource = MachineFilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
