<?php

namespace App\Filament\Resources\SmithReturnResource\Pages;

use App\Filament\Resources\SmithReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmithReturn extends EditRecord
{
    protected static string $resource = SmithReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
