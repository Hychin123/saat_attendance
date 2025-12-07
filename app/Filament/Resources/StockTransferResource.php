<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Filament\Resources\StockTransferResource\RelationManagers;
use App\Models\StockTransfer;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Stock Operations';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transfer Information')
                    ->schema([
                        Forms\Components\TextInput::make('reference_no')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        Forms\Components\Select::make('from_warehouse_id')
                            ->relationship('fromWarehouse', 'warehouse_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('to_warehouse_id')
                            ->relationship('toWarehouse', 'warehouse_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\DatePicker::make('transfer_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('requested_by')
                            ->relationship('requestedByUser', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approvedByUser', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending',
                                'APPROVED' => 'Approved',
                                'IN_TRANSIT' => 'In Transit',
                                'COMPLETED' => 'Completed',
                                'CANCELLED' => 'Cancelled',
                            ])
                            ->required()
                            ->default('PENDING'),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Items to Transfer')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->relationship('item', 'item_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                
                                Forms\Components\Select::make('from_location_id')
                                    ->label('From Location')
                                    ->options(function (Get $get) {
                                        $warehouseId = $get('../../from_warehouse_id');
                                        if (!$warehouseId) {
                                            return [];
                                        }
                                        return Location::where('warehouse_id', $warehouseId)
                                            ->where('is_active', true)
                                            ->pluck('location_code', 'id');
                                    })
                                    ->required()
                                    ->searchable(),
                                
                                Forms\Components\Select::make('to_location_id')
                                    ->label('To Location')
                                    ->options(function (Get $get) {
                                        $warehouseId = $get('../../to_warehouse_id');
                                        if (!$warehouseId) {
                                            return [];
                                        }
                                        return Location::where('warehouse_id', $warehouseId)
                                            ->where('is_active', true)
                                            ->pluck('location_code', 'id');
                                    })
                                    ->required()
                                    ->searchable(),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                
                                Forms\Components\TextInput::make('batch_number')
                                    ->label('Batch No.'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderable(false)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fromWarehouse.warehouse_name')
                    ->label('From Warehouse')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('toWarehouse.warehouse_name')
                    ->label('To Warehouse')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Qty')
                    ->getStateUsing(function ($record) {
                        return $record->items->sum('quantity');
                    })
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('transfer_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requestedByUser.name')
                    ->label('Requested By')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approvedByUser.name')
                    ->label('Approved By')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'APPROVED' => 'info',
                        'IN_TRANSIT' => 'primary',
                        'COMPLETED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
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
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'view' => Pages\ViewStockTransfer::route('/{record}'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
        ];
    }
}
