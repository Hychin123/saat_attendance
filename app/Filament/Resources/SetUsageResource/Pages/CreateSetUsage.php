<?php

namespace App\Filament\Resources\SetUsageResource\Pages;

use App\Filament\Resources\SetUsageResource;
use App\Models\Set;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSetUsage extends CreateRecord
{
    protected static string $resource = SetUsageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the user who is using the set
        $data['user_id'] = auth()->id();

        // Validate stock availability
        $set = Set::find($data['set_id']);
        $insufficientItems = $set->canUse($data['quantity'], $data['warehouse_id']);

        if (!empty($insufficientItems)) {
            $message = "Insufficient stock for:\n";
            foreach ($insufficientItems as $item) {
                $message .= "• {$item['item']}: Need {$item['required']}, Available {$item['available']} (Short {$item['shortage']})\n";
            }

            Notification::make()
                ->title('Insufficient Stock')
                ->body($message)
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Set used successfully. Stock has been deducted.';
    }
}
