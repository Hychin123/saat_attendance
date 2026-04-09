<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetUsageResource\Pages;
use App\Models\SetUsage;
use App\Models\Set;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class SetUsageResource extends Resource
{
    protected static ?string $model = SetUsage::class;

    protected static ?string$navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationGroup = 'Stock Operations';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Use Sets';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Set Usage Information')
                    ->schema([
                        Forms\Components\Select::make('set_id')
                            ->label('Set')
                            ->options(Set::where('is_active', true)->pluck('set_name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $setModel = Set::with('setItems.item')->find($state);
                                    if ($setModel) {
                                        $items = $setModel->setItems->map(fn($item) => 
                                            "• {$item->item->item_name}: {$item->quantity} {$item->unit}"
                                        )->join("\n");
                                        $set('items_preview', $items);
                                    }
                                }
                            }),
                        
                        Forms\Components\Placeholder::make('items_preview')
                            ->label('Items in this set')
                            ->content(fn ($get) => $get('items_preview') ?: 'Select a set to see items')
                            ->visible(fn ($get) => $get('set_id') !== null),
                        
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(Warehouse::pluck('warehouse_name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('quantity')
                            ->label('Number of Sets to Use')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('How many sets are you using?'),
                        
                        Forms\Components\DatePicker::make('usage_date')
                            ->label('Usage Date')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        
                        Forms\Components\Textarea::make('purpose')
                            ->label('Purpose')
                            ->rows(2)
                            ->placeholder('Why are you using this set?')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('set.set_code')
                    ->label('Set Code')
                    ->searchable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('set.set_name')
                    ->label('Set Name')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Sets Used')
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->label('Warehouse')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Used By')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Purpose')
                    ->limit(30)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('set_id')
                    ->label('Set')
                    ->options(Set::pluck('set_name', 'id'))
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::pluck('warehouse_name', 'id'))
                    ->searchable(),
                
                Tables\Filters\Filter::make('usage_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('usage_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('usage_date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('usage_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetUsages::route('/'),
            'create' => Pages\CreateSetUsage::route('/create'),
            'view' => Pages\ViewSetUsage::route('/{record}'),
        ];
    }
}
