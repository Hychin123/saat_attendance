<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockIn;
use App\Models\StockOut;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class WarehouseStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin() 
            || auth()->user()->hasPermission('view', 'warehouses')
            || auth()->user()->hasPermission('view', 'stocks')
            || auth()->user()->role?->name === 'HR Manager'
            || auth()->user()->role?->name === 'Warehouse Manager';
    }

    protected function getStats(): array
    {
        $totalItems = Item::where('is_active', true)->count();
        $totalStockQuantity = Stock::sum('quantity');
        $lowStockItems = Item::whereColumn('reorder_level', '>=', 
            DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE stocks.item_id = items.id)')
        )->count();
        
        $stockInsThisMonth = StockIn::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $stockOutsThisMonth = StockOut::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $expiringItems = Stock::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->count();
        
        return [
            Stat::make('Total Active Items', $totalItems)
                ->description('Items in master data')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            
            Stat::make('Total Stock Quantity', number_format($totalStockQuantity))
                ->description('Total units in warehouse')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),
            
            Stat::make('Low Stock Items', $lowStockItems)
                ->description('Items below reorder level')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockItems > 0 ? 'danger' : 'success'),
            
            Stat::make('Stock In (This Month)', $stockInsThisMonth)
                ->description('Received items')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),
            
            Stat::make('Stock Out (This Month)', $stockOutsThisMonth)
                ->description('Dispatched items')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('warning'),
            
            Stat::make('Expiring Soon', $expiringItems)
                ->description('Items expiring in 30 days')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($expiringItems > 0 ? 'warning' : 'success'),
        ];
    }
}
