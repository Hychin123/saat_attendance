<?php

namespace App\Filament\Resources\SmithStockIssueResource\Pages;

use App\Filament\Resources\SmithStockIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmithStockIssues extends ListRecords
{
    protected static string $resource = SmithStockIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
