<?php

namespace App\Filament\Resources\SmithStockIssueResource\Pages;

use App\Filament\Resources\SmithStockIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmithStockIssue extends EditRecord
{
    protected static string $resource = SmithStockIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
