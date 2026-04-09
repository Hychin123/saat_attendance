<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class StockMovementChart extends ChartWidget
{
    protected static ?string $heading = 'Stock Movement Trend (Last 30 Days)';
    
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin() 
            || auth()->user()->hasPermission('view', 'stock_movements')
            || auth()->user()->hasPermission('view', 'stocks')
            || auth()->user()->role?->name === 'HR Manager'
            || auth()->user()->role?->name === 'Warehouse Manager';
    }

    protected function getData(): array
    {
        $days = 30;
        $labels = [];
        $stockInData = [];
        $stockOutData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            
            // Get stock in for this date
            $stockIn = StockMovement::where('movement_type', 'IN')
                ->whereDate('movement_date', $date)
                ->sum('quantity');
            $stockInData[] = $stockIn ?? 0;
            
            // Get stock out for this date
            $stockOut = StockMovement::where('movement_type', 'OUT')
                ->whereDate('movement_date', $date)
                ->sum('quantity');
            $stockOutData[] = $stockOut ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Stock In',
                    'data' => $stockInData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                ],
                [
                    'label' => 'Stock Out',
                    'data' => $stockOutData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
