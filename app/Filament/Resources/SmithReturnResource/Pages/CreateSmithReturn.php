<?php

namespace App\Filament\Resources\SmithReturnResource\Pages;

use App\Filament\Resources\SmithReturnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmithReturn extends CreateRecord
{
    protected static string $resource = SmithReturnResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
