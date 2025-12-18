<?php

namespace App\Filament\Resources\MachineWaterUsageResource\Pages;

use App\Filament\Resources\MachineWaterUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMachineWaterUsages extends ListRecords
{
    protected static string $resource = MachineWaterUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
