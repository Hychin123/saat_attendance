<?php

namespace App\Filament\Resources\FilterReplacementResource\Pages;

use App\Filament\Resources\FilterReplacementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilterReplacement extends EditRecord
{
    protected static string $resource = FilterReplacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
