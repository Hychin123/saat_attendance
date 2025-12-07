<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationGroup = 'Warehouse Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Item Information')
                    ->schema([
                        Forms\Components\TextInput::make('item_code')
                            ->label('Item Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn () => Item::generateItemCode()),
                        
                        Forms\Components\TextInput::make('item_name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'category_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('category_name')
                                    ->required(),
                                Forms\Components\Textarea::make('description'),
                            ]),
                        
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'brand_name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('brand_name')
                                    ->required(),
                                Forms\Components\Textarea::make('description'),
                            ]),
                        
                        Forms\Components\Select::make('unit')
                            ->options([
                                'pcs' => 'Pieces',
                                'box' => 'Box',
                                'kg' => 'Kilogram',
                                'liter' => 'Liter',
                                'meter' => 'Meter',
                                'set' => 'Set',
                                'pack' => 'Pack',
                            ])
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\TextInput::make('barcode')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required(),
                        
                        Forms\Components\TextInput::make('selling_price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Stock Settings')
                    ->schema([
                        Forms\Components\TextInput::make('reorder_level')
                            ->numeric()
                            ->default(0)
                            ->helperText('Alert when stock falls below this level')
                            ->required(),
                        
                        Forms\Components\Toggle::make('has_expiry')
                            ->label('Has Expiry Date')
                            ->helperText('Enable if this item has an expiry date (e.g., food, medicine)'),
                        
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('items')
                            ->maxSize(2048),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->select('items.*')
                    ->selectRaw('(SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE stocks.item_id = items.id) as current_stock')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                
                Tables\Columns\TextColumn::make('item_code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('category.category_name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('brand.brand_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('unit')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('cost_price')
                    ->money('usd')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('usd')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stock Qty')
                    ->badge()
                    ->color(fn ($record) => $record->current_stock <= $record->reorder_level ? 'danger' : 'success')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reorder_level')
                    ->label('Reorder Level')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('has_expiry')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'category_name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('brand_id')
                    ->relationship('brand', 'brand_name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('has_expiry')
                    ->label('Has Expiry'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereRaw('reorder_level >= (SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE stocks.item_id = items.id)')
                    )
                    ->toggle(),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view' => Pages\ViewItem::route('/{record}'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
