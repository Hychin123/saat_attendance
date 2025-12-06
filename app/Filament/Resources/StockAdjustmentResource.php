<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Models\StockAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference_no')
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Auto-generated'),
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'warehouse_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'location_code')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('item_id')
                    ->relationship('item', 'item_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('adjustment_type')
                    ->options([
                        'add' => 'Add',
                        'subtract' => 'Subtract',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('batch_number')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('adjustment_date')
                    ->required()
                    ->default(now()),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('adjusted_by')
                    ->relationship('adjustedByUser', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('approved_by')
                    ->relationship('approvedByUser', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.location_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.item_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('adjustment_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'add' => 'success',
                        'subtract' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('adjustment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
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
            'index' => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            'view' => Pages\ViewStockAdjustment::route('/{record}'),
            'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
        ];
    }
}
