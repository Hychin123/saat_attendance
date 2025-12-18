<?php

namespace App\Filament\Resources\MachineFilterResource\Pages;

use App\Filament\Resources\MachineFilterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMachineFilter extends EditRecord
{
    protected static string $resource = MachineFilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
