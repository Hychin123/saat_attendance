<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmithReturnResource\Pages;
use App\Models\SmithReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmithReturnResource extends Resource
{
    protected static ?string $model = SmithReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Smith Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Smith Returns';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Return Information')
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
                        Forms\Components\DatePicker::make('return_date')
                            ->required()
                            ->default(now()),
                    ])->columns(2),
                Forms\Components\Section::make('Defective Item')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Defective Item')
                            ->options(function (Forms\Get $get) {
                                $warehouseId = $get('warehouse_id');
                                
                                if (!$warehouseId) {
                                    return [];
                                }
                                
                                // Get all items from the selected warehouse
                                return \App\Models\Item::whereHas('stocks', function ($query) use ($warehouseId) {
                                    $query->where('warehouse_id', $warehouseId);
                                })->pluck('item_name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select a warehouse first'),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Defective Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01),
                        Forms\Components\Select::make('return_reason')
                            ->options([
                                'defective' => 'Defective',
                                'damaged' => 'Damaged',
                                'wrong_item' => 'Wrong Item',
                                'quality_issue' => 'Quality Issue',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),
                Forms\Components\Section::make('Replacement Item')
                    ->schema([
                        Forms\Components\Select::make('replacement_item_id')
                            ->label('Replacement Item')
                            ->options(function (Forms\Get $get) {
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
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    $warehouseId = $get('warehouse_id');
                                    if ($warehouseId) {
                                        $stock = \App\Models\Stock::where('item_id', $state)
                                            ->where('warehouse_id', $warehouseId)
                                            ->sum('quantity');
                                        
                                        $set('replacement_available_stock', $stock);
                                    }
                                }
                            })
                            ->helperText(function (Forms\Get $get) {
                                $stock = $get('replacement_available_stock');
                                if ($stock !== null) {
                                    return "Available stock: {$stock}";
                                }
                                return 'Select a warehouse first';
                            }),
                        Forms\Components\Hidden::make('replacement_available_stock'),
                        Forms\Components\TextInput::make('replacement_quantity')
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->live()
                            ->helperText(function (Forms\Get $get) {
                                $availableStock = $get('replacement_available_stock');
                                $quantity = $get('replacement_quantity');
                                
                                if ($availableStock !== null && $quantity > $availableStock) {
                                    return "⚠️ Exceeds available stock ({$availableStock})";
                                }
                                return null;
                            }),
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Status & Approval')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->default('pending')
                            ->required()
                            ->disabled(fn() => !auth()->user()->can('approve', SmithReturn::class)),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->disabled(),
                        Forms\Components\Select::make('processed_by')
                            ->relationship('processor', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('processed_at')
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
                    ->label('Defective Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('replacementItem.item_name')
                    ->label('Replacement')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('return_reason')
                    ->colors([
                        'danger' => 'defective',
                        'warning' => 'damaged',
                        'info' => 'wrong_item',
                        'secondary' => 'quality_issue',
                        'gray' => 'other',
                    ]),
                Tables\Columns\TextColumn::make('return_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'completed',
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
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('return_reason')
                    ->options([
                        'defective' => 'Defective',
                        'damaged' => 'Damaged',
                        'wrong_item' => 'Wrong Item',
                        'quality_issue' => 'Quality Issue',
                        'other' => 'Other',
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
            'index' => Pages\ListSmithReturns::route('/'),
            'create' => Pages\CreateSmithReturn::route('/create'),
            'view' => Pages\ViewSmithReturn::route('/{record}'),
            'edit' => Pages\EditSmithReturn::route('/{record}/edit'),
        ];
    }
}
