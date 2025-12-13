<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Stock;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Stock Operations';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('item_id')
                    ->relationship('item', 'item_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'warehouse_name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('location_id')
                    ->label('Location')
                    ->options(function (Get $get) {
                        $warehouseId = $get('warehouse_id');
                        if (!$warehouseId) {
                            return [];
                        }
                        return Location::where('warehouse_id', $warehouseId)
                            ->where('is_active', true)
                            ->pluck('location_code', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Forms\Components\TextInput::make('batch_number')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\DateTimePicker::make('last_updated')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.item_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.location_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'view' => Pages\ViewStock::route('/{record}'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
