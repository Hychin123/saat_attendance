<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\ShiftResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShift extends CreateRecord
{
    protected static string $resource = ShiftResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
