<?php

namespace App\Filament\Resources\SetUsageResource\Pages;

use App\Filament\Resources\SetUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSetUsages extends ListRecords
{
    protected static string $resource = SetUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
