<?php

namespace App\Filament\Resources\MaterialAdjustmentResource\Pages;

use App\Filament\Resources\MaterialAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialAdjustment extends CreateRecord
{
    protected static string $resource = MaterialAdjustmentResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
