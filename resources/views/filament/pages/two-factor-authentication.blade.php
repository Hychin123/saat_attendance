<x-filament-panels::page>
    <x-filament-panels::form wire:submit="{{auth()->user()->google2fa_enabled ? 'disableTwoFactor' : 'enableTwoFactor'}}">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
