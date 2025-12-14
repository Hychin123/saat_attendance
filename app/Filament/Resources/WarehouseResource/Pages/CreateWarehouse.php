<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
