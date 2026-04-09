<?php

namespace App\Filament\Resources\SmithReturnResource\Pages;

use App\Filament\Resources\SmithReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmithReturns extends ListRecords
{
    protected static string $resource = SmithReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
