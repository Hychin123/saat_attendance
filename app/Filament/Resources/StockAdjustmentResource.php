<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Models\StockAdjustment;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Stock Operations';

    protected static ?int $navigationSort = 4;

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
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                        // Recalculate available stock when location changes
                        $itemId = $get('item_id');
                        $warehouseId = $get('warehouse_id');
                        $batchNumber = $get('batch_number');
                        
                        if ($itemId && $warehouseId) {
                            $query = \App\Models\Stock::where('item_id', $itemId)
                                ->where('warehouse_id', $warehouseId);
                                
                            if ($state) {
                                $query->where('location_id', $state);
                            }
                            
                            if ($batchNumber) {
                                $query->where('batch_number', $batchNumber);
                            }
                            
                            $stock = $query->sum('quantity');
                            $set('available_stock', $stock);
                        }
                    }),
                Forms\Components\Select::make('item_id')
                    ->label('Item')
                    ->options(function (Get $get) {
                        $warehouseId = $get('warehouse_id');
                        if (!$warehouseId) {
                            return [];
                        }
                        
                        // Get items that have stock in the selected warehouse
                        return \App\Models\Item::whereHas('stocks', function ($query) use ($warehouseId) {
                            $query->where('warehouse_id', $warehouseId)
                                  ->where('quantity', '>', 0);
                        })->pluck('item_name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                        if ($state) {
                            // Get available stock quantity
                            $warehouseId = $get('warehouse_id');
                            $locationId = $get('location_id');
                            $batchNumber = $get('batch_number');
                            
                            if ($warehouseId) {
                                $query = \App\Models\Stock::where('item_id', $state)
                                    ->where('warehouse_id', $warehouseId);
                                    
                                if ($locationId) {
                                    $query->where('location_id', $locationId);
                                }
                                
                                if ($batchNumber) {
                                    $query->where('batch_number', $batchNumber);
                                }
                                
                                $stock = $query->sum('quantity');
                                $set('available_stock', $stock);
                            }
                        }
                    })
                    ->helperText(function (Get $get) {
                        $availableStock = $get('available_stock');
                        if ($availableStock !== null) {
                            return "Current stock: {$availableStock}";
                        }
                        return null;
                    }),
                Forms\Components\Hidden::make('available_stock'),
                Forms\Components\Select::make('adjustment_type')
                    ->options([
                        'add' => 'Add',
                        'subtract' => 'Subtract',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live()
                    ->maxValue(function (Get $get) {
                        $adjustmentType = $get('adjustment_type');
                        $availableStock = $get('available_stock');
                        
                        // Only limit for subtract operations
                        if ($adjustmentType === 'subtract' && $availableStock !== null) {
                            return $availableStock;
                        }
                        return 999999;
                    })
                    ->helperText(function (Get $get) {
                        $adjustmentType = $get('adjustment_type');
                        $availableStock = $get('available_stock');
                        $quantity = $get('quantity');
                        
                        if ($adjustmentType === 'subtract' && $availableStock !== null && $quantity > $availableStock) {
                            return "⚠️ Exceeds current stock ({$availableStock})";
                        }
                        return null;
                    }),
                Forms\Components\TextInput::make('batch_number')
                    ->maxLength(255)
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                        // Recalculate available stock when batch changes
                        $itemId = $get('item_id');
                        $warehouseId = $get('warehouse_id');
                        $locationId = $get('location_id');
                        
                        if ($itemId && $warehouseId) {
                            $query = \App\Models\Stock::where('item_id', $itemId)
                                ->where('warehouse_id', $warehouseId);
                                
                            if ($locationId) {
                                $query->where('location_id', $locationId);
                            }
                            
                            if ($state) {
                                $query->where('batch_number', $state);
                            }
                            
                            $stock = $query->sum('quantity');
                            $set('available_stock', $stock);
                        }
                    }),
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
