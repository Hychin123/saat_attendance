<?php

namespace App\Filament\Resources\StockResource\Widgets;

use Filament\Widgets\ChartWidget;

class StockOverview extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
