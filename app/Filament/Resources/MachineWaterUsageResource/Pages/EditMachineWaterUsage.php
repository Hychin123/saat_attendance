<?php

namespace App\Filament\Resources\MachineWaterUsageResource\Pages;

use App\Filament\Resources\MachineWaterUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMachineWaterUsage extends EditRecord
{
    protected static string $resource = MachineWaterUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
