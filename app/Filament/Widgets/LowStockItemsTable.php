<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\Stock;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LowStockItemsTable extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin() 
            || auth()->user()->hasPermission('view', 'stocks')
            || auth()->user()->hasPermission('view', 'items')
            || auth()->user()->role?->name === 'HR Manager'
            || auth()->user()->role?->name === 'Warehouse Manager';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Item::query()
                    ->with(['category', 'stocks'])
                    ->select('items.*')
                    ->selectRaw('(SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE stocks.item_id = items.id) as current_stock')
                    ->whereRaw('reorder_level >= (SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE stocks.item_id = items.id)')
                    ->where('is_active', true)
            )
            ->heading('Low Stock Items')
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->label('Item Code')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item Name')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('category.category_name')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reorder_level')
                    ->label('Reorder Level')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unit')
                    ->badge(),
            ])
            ->defaultSort('current_stock', 'asc');
    }
}
