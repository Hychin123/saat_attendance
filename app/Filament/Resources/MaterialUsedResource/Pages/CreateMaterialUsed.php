<?php

namespace App\Filament\Resources\MaterialUsedResource\Pages;

use App\Filament\Resources\MaterialUsedResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialUsed extends CreateRecord
{
    protected static string $resource = MaterialUsedResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
