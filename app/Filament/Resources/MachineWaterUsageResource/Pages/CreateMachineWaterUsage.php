<?php

namespace App\Filament\Resources\MachineWaterUsageResource\Pages;

use App\Filament\Resources\MachineWaterUsageResource;
use App\Models\Machine;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateMachineWaterUsage extends CreateRecord
{
    protected static string $resource = MachineWaterUsageResource::class;
    
    protected function afterCreate(): void
    {
        $machine = Machine::find($this->record->machine_id);
        
        if ($machine) {
            // Update all active filters with this usage
            $activeFilters = $machine->activeFilters;
            $filtersUpdated = 0;
            $filtersNeedChange = 0;
            
            foreach ($activeFilters as $filter) {
                $filter->addUsage($this->record->liters_dispensed);
                $filtersUpdated++;
                
                if ($filter->status === 'need_change') {
                    $filtersNeedChange++;
                }
            }
            
            // Show notification
            if ($filtersNeedChange > 0) {
                Notification::make()
                    ->title('Water usage recorded')
                    ->body("{$filtersUpdated} filters updated. ⚠️ {$filtersNeedChange} filter(s) need replacement!")
                    ->warning()
                    ->duration(10000)
                    ->send();
            } else {
                Notification::make()
                    ->title('Water usage recorded successfully')
                    ->body("{$this->record->liters_dispensed}L recorded. {$filtersUpdated} filters updated.")
                    ->success()
                    ->send();
            }
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
