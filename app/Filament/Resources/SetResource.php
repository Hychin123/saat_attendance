<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetResource\Pages;
use App\Models\Set;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SetResource extends Resource
{
    protected static ?string $model = Set::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Warehouse Management';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Sets/Kits';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Set Information')
                    ->schema([
                        Forms\Components\TextInput::make('set_code')
                            ->label('Set Code')
                            ->default(fn() => Set::generateSetCode())
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('set_name')
                            ->label('Set Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Installation Kit A'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Set Items')
                    ->schema([
                        Forms\Components\Repeater::make('setItems')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(Item::where('is_active', true)->pluck('item_name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->columnSpan(1),
                                
                                Forms\Components\TextInput::make('unit')
                                    ->label('Unit')
                                    ->maxLength(50)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                Item::find($state['item_id'])?->item_name ?? null
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('set_code')
                    ->label('Set Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('set_name')
                    ->label('Set Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('setItems_count')
                    ->counts('setItems')
                    ->label('Items Count')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Total Quantity')
                    ->getStateUsing(fn (Set $record) => $record->setItems->sum('quantity'))
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('usages_count')
                    ->counts('usages')
                    ->label('Times Used')
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All sets')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSets::route('/'),
            'create' => Pages\CreateSet::route('/create'),
            'view' => Pages\ViewSet::route('/{record}'),
            'edit' => Pages\EditSet::route('/{record}/edit'),
        ];
    }
}
