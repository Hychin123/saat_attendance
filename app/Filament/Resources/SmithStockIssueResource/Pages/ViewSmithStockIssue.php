<?php

namespace App\Filament\Resources\SmithStockIssueResource\Pages;

use App\Filament\Resources\SmithStockIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSmithStockIssue extends ViewRecord
{
    protected static string $resource = SmithStockIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
