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
                                    ->relationship('item', 'item_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                
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
                                    ->columnSpan(2),
                                
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
