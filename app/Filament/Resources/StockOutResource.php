<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOutResource\Pages;
use App\Filament\Resources\StockOutResource\RelationManagers;
use App\Models\StockOut;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;

class StockOutResource extends Resource
{
    protected static ?string $model = StockOut::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    
    protected static ?string $navigationGroup = 'Stock Operations';
    
    protected static ?string $navigationLabel = 'Stock Out (Dispatch)';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stock Out Information')
                    ->schema([
                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference No.')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn () => StockOut::generateReferenceNo()),
                        
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'warehouse_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        
                        Forms\Components\TextInput::make('customer_department')
                            ->label('Customer / Department')
                            ->required(),
                        
                        Forms\Components\DatePicker::make('dispatch_date')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\Select::make('issued_by')
                            ->relationship('issuedByUser', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->searchable(),
                        
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approvedByUser', 'name')
                            ->searchable(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending',
                                'APPROVED' => 'Approved',
                                'DISPATCHED' => 'Dispatched',
                                'CANCELLED' => 'Cancelled',
                            ])
                            ->default('PENDING')
                            ->required(),
                        
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Items to Dispatch')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (Get $get) {
                                        $warehouseId = $get('../../warehouse_id');
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
                                            $warehouseId = $get('../../warehouse_id');
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
                                            return "Available stock: {$availableStock}";
                                        }
                                        return null;
                                    })
                                    ->columnSpan(2),
                                
                                Forms\Components\Hidden::make('available_stock'),
                                
                                Forms\Components\Select::make('location_id')
                                    ->label('From Location')
                                    ->options(function (Get $get) {
                                        $warehouseId = $get('../../warehouse_id');
                                        if (!$warehouseId) {
                                            return [];
                                        }
                                        return Location::where('warehouse_id', $warehouseId)
                                            ->where('is_active', true)
                                            ->pluck('location_code', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                        // Recalculate available stock when location changes
                                        $itemId = $get('item_id');
                                        $warehouseId = $get('../../warehouse_id');
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
                                    })
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->maxValue(function (Get $get) {
                                        $availableStock = $get('available_stock');
                                        return $availableStock ?? 999999;
                                    })
                                    ->helperText(function (Get $get) {
                                        $availableStock = $get('available_stock');
                                        $quantity = $get('quantity');
                                        
                                        if ($availableStock !== null && $quantity > $availableStock) {
                                            return "⚠️ Exceeds available stock ({$availableStock})";
                                        }
                                        return null;
                                    }),
                                
                                Forms\Components\TextInput::make('batch_number')
                                    ->label('Batch No.')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Get $get) {
                                        // Recalculate available stock when batch changes
                                        $itemId = $get('item_id');
                                        $warehouseId = $get('../../warehouse_id');
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
                
                Tables\Columns\TextColumn::make('customer_department')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('dispatch_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('issuedByUser.name')
                    ->label('Issued By')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PENDING',
                        'info' => 'APPROVED',
                        'success' => 'DISPATCHED',
                        'danger' => 'CANCELLED',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'APPROVED' => 'Approved',
                        'DISPATCHED' => 'Dispatched',
                        'CANCELLED' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'warehouse_name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListStockOuts::route('/'),
            'create' => Pages\CreateStockOut::route('/create'),
            'view' => Pages\ViewStockOut::route('/{record}'),
            'edit' => Pages\EditStockOut::route('/{record}/edit'),
        ];
    }
}
