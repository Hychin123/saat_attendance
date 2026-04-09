<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialUsedResource\Pages;
use App\Models\MaterialUsed;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialUsedResource extends Resource
{
    protected static ?string $model = MaterialUsed::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Smith Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Material Used';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Material Information')
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
                                    $query->where('warehouse_id', $warehouseId)
                                          ->where('quantity', '>', 0);
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
                                        
                                        $set('available_stock', $stock);
                                    }
                                }
                            })
                            ->helperText(function (Forms\Get $get) {
                                $availableStock = $get('available_stock');
                                if ($availableStock !== null) {
                                    return "Available stock: {$availableStock}";
                                }
                                return 'Select a warehouse first';
                            }),
                        Forms\Components\Hidden::make('available_stock'),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->live()
                            ->helperText(function (Forms\Get $get) {
                                $availableStock = $get('available_stock');
                                $quantity = $get('quantity');
                                
                                if ($availableStock !== null && $quantity > $availableStock) {
                                    return "⚠️ Exceeds available stock ({$availableStock})";
                                }
                                return null;
                            }),
                        Forms\Components\TextInput::make('unit')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Usage Details')
                    ->schema([
                        Forms\Components\DatePicker::make('usage_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('project_name')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('purpose')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
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
                            ->disabled(fn() => !auth()->user()->can('approve', MaterialUsed::class)),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
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
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project_name')
                    ->searchable()
                    ->toggleable(),
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
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Smith')
                    ->relationship('user', 'name'),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'warehouse_name'),
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
            'index' => Pages\ListMaterialUseds::route('/'),
            'create' => Pages\CreateMaterialUsed::route('/create'),
            'view' => Pages\ViewMaterialUsed::route('/{record}'),
            'edit' => Pages\EditMaterialUsed::route('/{record}/edit'),
        ];
    }
}
