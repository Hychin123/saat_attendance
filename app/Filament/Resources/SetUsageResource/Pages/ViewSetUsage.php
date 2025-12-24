<?php

namespace App\Filament\Resources\SetUsageResource\Pages;

use App\Filament\Resources\SetUsageResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewSetUsage extends ViewRecord
{
    protected static string $resource = SetUsageResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Usage Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('usage_date')
                            ->label('Date')
                            ->date(),
                        Infolists\Components\TextEntry::make('set.set_name')
                            ->label('Set Used'),
                        Infolists\Components\TextEntry::make('quantity')
                            ->label('Number of Sets')
                            ->badge(),
                        Infolists\Components\TextEntry::make('warehouse.warehouse_name')
                            ->label('Warehouse'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Used By'),
                        Infolists\Components\TextEntry::make('purpose')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Items Deducted')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('set.setItems')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('item.item_name')
                                    ->label('Item'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Qty per Set'),
                                Infolists\Components\TextEntry::make('total')
                                    ->label('Total Deducted')
                                    ->state(function ($record, $livewire) {
                                        return $record->quantity * $livewire->record->quantity;
                                    })
                                    ->badge()
                                    ->color('danger'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
