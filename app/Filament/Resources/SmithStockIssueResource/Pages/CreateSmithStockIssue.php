<?php

namespace App\Filament\Resources\SmithStockIssueResource\Pages;

use App\Filament\Resources\SmithStockIssueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmithStockIssue extends CreateRecord
{
    protected static string $resource = SmithStockIssueResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
