<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialAdjustmentResource\Pages;
use App\Models\MaterialAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialAdjustmentResource extends Resource
{
    protected static ?string $model = MaterialAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';

    protected static ?string $navigationGroup = 'Smith Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Material Adjustments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Adjustment Information')
                    ->schema([
                        Forms\Components\TextInput::make('reference_no')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        Forms\Components\Select::make('user_id')
                            ->label('Smith')
                            ->relationship('user', 'name', function (Builder $query) {
                                $query->whereHas('role', function ($q) {
                                    $q->where('name', 'Smith');
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn() => auth()->user()->role?->name === 'Smith' ? auth()->id() : null),
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'warehouse_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('item_id')
                            ->label('Item')
                            ->options(function (Forms\Get $get) {
                                $warehouseId = $get('warehouse_id');
                                
                                if (!$warehouseId) {
                                    return [];
                                }
                                
                                // Get items that have stock in the selected warehouse
                                return \App\Models\Item::whereHas('stocks', function ($query) use ($warehouseId) {
                                    $query->where('warehouse_id', $warehouseId);
                                })->pluck('item_name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    // Get available stock quantity
                                    $warehouseId = $get('warehouse_id');
                                    if ($warehouseId) {
                                        $stock = \App\Models\Stock::where('item_id', $state)
                                            ->where('warehouse_id', $warehouseId)
                                            ->sum('quantity');
                                        
                                        $set('previous_quantity', $stock);
                                        $set('available_stock', $stock);
                                    }
                                }
                            })
                            ->helperText(function (Forms\Get $get) {
                                $availableStock = $get('available_stock');
                                if ($availableStock !== null) {
                                    return "Current stock: {$availableStock}";
                                }
                                return 'Select a warehouse first';
                            }),
                        Forms\Components\Select::make('adjustment_type')
                            ->options([
                                'add' => 'Add',
                                'subtract' => 'Subtract',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Hidden::make('available_stock'),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $adjustmentType = $get('adjustment_type');
                                $previousQty = $get('previous_quantity') ?? 0;
                                
                                if ($adjustmentType === 'add') {
                                    $set('new_quantity', $previousQty + $state);
                                } elseif ($adjustmentType === 'subtract') {
                                    $set('new_quantity', max(0, $previousQty - $state));
                                }
                            })
                            ->helperText(function (Forms\Get $get) {
                                $availableStock = $get('available_stock');
                                $quantity = $get('quantity');
                                $adjustmentType = $get('adjustment_type');
                                
                                if ($adjustmentType === 'subtract' && $availableStock !== null && $quantity > $availableStock) {
                                    return "⚠️ Cannot subtract more than available stock ({$availableStock})";
                                }
                                return null;
                            }),
                    ])->columns(2),
                Forms\Components\Section::make('Quantity Tracking')
                    ->schema([
                        Forms\Components\TextInput::make('previous_quantity')
                            ->required()
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\TextInput::make('new_quantity')
                            ->required()
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\DatePicker::make('adjustment_date')
                            ->required()
                            ->default(now()),
                    ])->columns(3),
                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Approval')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required()
                            ->disabled(fn() => !auth()->user()->can('approve', MaterialAdjustment::class)),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->disabled(),
                    ])->columns(3)->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Smith')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.item_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('adjustment_type')
                    ->colors([
                        'success' => 'add',
                        'danger' => 'subtract',
                    ]),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('previous_quantity')
                    ->numeric()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('new_quantity')
                    ->numeric()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('adjustment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('adjustment_type')
                    ->options([
                        'add' => 'Add',
                        'subtract' => 'Subtract',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Smith')
                    ->relationship('user', 'name'),
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
            'index' => Pages\ListMaterialAdjustments::route('/'),
            'create' => Pages\CreateMaterialAdjustment::route('/create'),
            'view' => Pages\ViewMaterialAdjustment::route('/{record}'),
            'edit' => Pages\EditMaterialAdjustment::route('/{record}/edit'),
        ];
    }
}
