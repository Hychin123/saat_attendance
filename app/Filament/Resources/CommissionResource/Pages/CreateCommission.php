<?php

namespace App\Filament\Resources\CommissionResource\Pages;

use App\Filament\Resources\CommissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommission extends CreateRecord
{
    protected static string $resource = CommissionResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
