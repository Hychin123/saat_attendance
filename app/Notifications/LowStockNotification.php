<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class LowStockNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Item $item,
        public int $currentStock
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'item_id' => $this->item->id,
            'item_code' => $this->item->item_code,
            'item_name' => $this->item->item_name,
            'current_stock' => $this->currentStock,
            'reorder_level' => $this->item->reorder_level,
            'unit' => $this->item->unit,
            'message' => "Low stock alert: {$this->item->item_name} ({$this->item->item_code}) has only {$this->currentStock} {$this->item->unit} remaining. Reorder level is {$this->item->reorder_level}.",
        ];
    }

    /**
     * Get the Filament notification representation.
     */
    public function toFilament(object $notifiable): FilamentNotification
    {
        return FilamentNotification::make()
            ->warning()
            ->title('Low Stock Alert')
            ->body("**{$this->item->item_name}** ({$this->item->item_code}) is running low.")
            ->icon('heroicon-o-exclamation-triangle')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('View Item')
                    ->url(route('filament.admin.resources.items.edit', ['record' => $this->item->id])),
            ]);
    }
}
