<?php

namespace App\Filament\Resources\SetResource\Pages;

use App\Filament\Resources\SetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewSet extends ViewRecord
{
    protected static string $resource = SetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Set Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('set_code')
                            ->label('Set Code'),
                        Infolists\Components\TextEntry::make('set_name')
                            ->label('Set Name'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Items in Set')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('setItems')
                            ->schema([
                                Infolists\Components\TextEntry::make('item.item_name')
                                    ->label('Item'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Quantity'),
                                Infolists\Components\TextEntry::make('unit')
                                    ->label('Unit'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
