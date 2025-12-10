<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\SelectFilter;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationGroup = 'Sales Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Sale Information')
                    ->schema([
                        Forms\Components\TextInput::make('sale_id')
                            ->label('Sale ID')
                            ->default(fn() => Sale::generateSaleId())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(User::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('agent_id')
                            ->label('Sales Agent (5% Commission)')
                            ->options(User::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Agent will receive 5% commission when sale is completed')
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(Warehouse::whereNotNull('warehouse_name')->pluck('warehouse_name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            // ->afterStateUpdated(function ($state, callable $set) {
                            //     // Reset items when warehouse changes
                            //     $set('items', []);
                            // })
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('status')
                            ->options(Sale::getStatuses())
                            ->default(Sale::STATUS_PENDING)
                            ->required()
                            ->reactive()
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('expected_ready_date')
                            ->label('Expected Ready Date')
                            ->default(now()->addWeek())
                            ->helperText('Typically ~1 week after order')
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('completed_date')
                            ->label('Completed Date')
                            ->visible(fn($get) => $get('status') === Sale::STATUS_COMPLETED)
                            ->columnSpan(1),
                    ])->columns(2),

                Section::make('Sale Items')
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (callable $get) {
                                        $warehouseId = $get('../../warehouse_id');
                                        
                                        if (!$warehouseId) {
                                            return Item::whereNotNull('item_name')->pluck('item_name', 'id');
                                        }
                                        
                                        // Get items that have stock in the selected warehouse
                                        return Item::whereHas('stocks', function ($query) use ($warehouseId) {
                                            $query->where('warehouse_id', $warehouseId)
                                                  ->where('quantity', '>', 0);
                                        })->pluck('item_name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                                        if ($state) {
                                            $item = Item::find($state);
                                            $set('unit_price', $item?->selling_price ?? 0);
                                            
                                            // Get available stock quantity
                                            $warehouseId = $get('../../warehouse_id');
                                            if ($warehouseId) {
                                                $stock = \App\Models\Stock::where('item_id', $state)
                                                    ->where('warehouse_id', $warehouseId)
                                                    ->sum('quantity');
                                                
                                                $set('available_stock', $stock);
                                            }
                                        }
                                    })
                                    ->helperText(function (callable $get) {
                                        $availableStock = $get('available_stock');
                                        if ($availableStock !== null) {
                                            return "Available stock: {$availableStock}";
                                        }
                                        return null;
                                    })
                                    ->columnSpan(2),
                                
                                Forms\Components\Hidden::make('available_stock'),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->reactive()
                                    ->minValue(1)
                                    ->maxValue(function (callable $get) {
                                        $availableStock = $get('available_stock');
                                        return $availableStock ?? 999999;
                                    })
                                    ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $set('total_price', $state * $unitPrice);
                                    })
                                    ->helperText(function (callable $get) {
                                        $availableStock = $get('available_stock');
                                        $quantity = $get('quantity');
                                        
                                        if ($availableStock !== null && $quantity > $availableStock) {
                                            return "⚠️ Exceeds available stock ({$availableStock})";
                                        }
                                        return null;
                                    })
                                    ->columnSpan(1),
                                
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                        $quantity = $get('quantity') ?? 0;
                                        $set('total_price', $quantity * $state);
                                    })
                                    ->columnSpan(1),
                                
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                
                                Forms\Components\Textarea::make('notes')
                                    ->columnSpanFull(),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderable(false),
                    ]),

                Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('discount')
                            ->label('Discount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('tax')
                            ->label('Tax')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('net_total')
                            ->label('Net Total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('deposit_amount')
                            ->label('Deposit Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->helperText('Customer must deposit before processing')
                            ->reactive()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('remaining_amount')
                            ->label('Remaining Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_id')
                    ->label('Sale ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->label('Warehouse')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('net_total')
                    ->label('Net Total')
                    ->money('usd')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('usd'),
                    ]),
                
                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Deposit')
                    ->money('usd')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Remaining')
                    ->money('usd')
                    ->color(fn($state) => $state > 0 ? 'warning' : 'success')
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => Sale::STATUS_PENDING,
                        'info' => Sale::STATUS_DEPOSITED,
                        'warning' => Sale::STATUS_PROCESSING,
                        'primary' => Sale::STATUS_READY,
                        'success' => Sale::STATUS_COMPLETED,
                        'danger' => Sale::STATUS_CANCELLED,
                        'gray' => Sale::STATUS_REFUNDED,
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('expected_ready_date')
                    ->label('Expected Ready')
                    ->date()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(Sale::getStatuses())
                    ->multiple(),
                
                SelectFilter::make('agent_id')
                    ->label('Sales Agent')
                    ->options(User::whereNotNull('name')->pluck('name', 'id'))
                    ->searchable(),
                
                SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::whereNotNull('warehouse_name')->pluck('warehouse_name', 'id'))
                    ->searchable(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
