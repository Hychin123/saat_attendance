<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Filament\Resources\StockMovementResource\RelationManagers;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('item_id')
                    ->relationship('item', 'item_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('movement_type')
                    ->options([
                        'in' => 'In',
                        'out' => 'Out',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Adjustment',
                    ])
                    ->required(),
                Forms\Components\Select::make('from_warehouse_id')
                    ->relationship('fromWarehouse', 'warehouse_name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('to_warehouse_id')
                    ->relationship('toWarehouse', 'warehouse_name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('from_location_id')
                    ->relationship('fromLocation', 'location_code')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('to_location_id')
                    ->relationship('toLocation', 'location_code')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('reference_no')
                    ->maxLength(255),
                Forms\Components\TextInput::make('batch_number')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\DatePicker::make('movement_date')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.item_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'transfer' => 'info',
                        'adjustment' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('fromWarehouse.warehouse_name')
                    ->label('From Warehouse')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('toWarehouse.warehouse_name')
                    ->label('To Warehouse')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
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
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
            'edit' => Pages\EditStockMovement::route('/{record}/edit'),
        ];
    }
}
