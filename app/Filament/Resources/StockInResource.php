<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockInResource\Pages;
use App\Filament\Resources\StockInResource\RelationManagers;
use App\Models\StockIn;
use App\Models\Warehouse;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;

class StockInResource extends Resource
{
    protected static ?string $model = StockIn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    
    protected static ?string $navigationGroup = 'Stock Operations';
    
    protected static ?string $navigationLabel = 'Stock In (Receive)';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stock In Information')
                    ->schema([
                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference No.')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn () => StockIn::generateReferenceNo()),
                        
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'supplier_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('supplier_name')
                                    ->required(),
                                Forms\Components\TextInput::make('phone'),
                                Forms\Components\TextInput::make('email')->email(),
                                Forms\Components\Textarea::make('address'),
                                Forms\Components\TextInput::make('contact_person'),
                            ]),
                        
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'warehouse_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        
                        Forms\Components\DatePicker::make('received_date')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\Select::make('received_by')
                            ->relationship('receivedByUser', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->searchable(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending',
                                'RECEIVED' => 'Received',
                                'CANCELLED' => 'Cancelled',
                            ])
                            ->default('PENDING')
                            ->required(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Items')
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
                                
                                Forms\Components\Select::make('location_id')
                                    ->label('Location (Rack/Shelf/Bin)')
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
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                
                                Forms\Components\TextInput::make('batch_number')
                                    ->label('Batch No.'),
                                
                                Forms\Components\DatePicker::make('expiry_date'),
                                
                                Forms\Components\TextInput::make('unit_cost')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(4)
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
                
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('receivedByUser.name')
                    ->label('Received By')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'RECEIVED',
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
                        'RECEIVED' => 'Received',
                        'CANCELLED' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'warehouse_name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('received_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_date', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ListStockIns::route('/'),
            'create' => Pages\CreateStockIn::route('/create'),
            'view' => Pages\ViewStockIn::route('/{record}'),
            'edit' => Pages\EditStockIn::route('/{record}/edit'),
        ];
    }
}
