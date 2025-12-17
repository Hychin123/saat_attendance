<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapTableWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Cheesegrits\FilamentGoogleMaps\Filters\MapIsFilter;
use Cheesegrits\FilamentGoogleMaps\Actions\GoToAction;

class SalesMapWidget extends MapTableWidget
{
    protected static ?string $heading = 'Sales Locations';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '500px';

    protected static ?bool $clustering = true;

    protected static ?bool $fitToBounds = true;

    protected static ?string $markerAction = 'markerAction';

    protected function getTableQuery(): Builder
    {
        return Sale::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['customer', 'agent', 'warehouse']);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('sale_id')
                ->label('Sale ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('customer_name')
                ->label('Customer')
                ->searchable()
                ->default(fn($record) => $record->customer_name ?? $record->customer?->name ?? 'N/A'),

            Tables\Columns\TextColumn::make('customer_phone')
                ->label('Phone')
                ->searchable(),

            Tables\Columns\TextColumn::make('customer_location')
                ->label('Location')
                ->searchable()
                ->limit(30),

            Tables\Columns\TextColumn::make('agent.name')
                ->label('Agent')
                ->searchable(),

            Tables\Columns\TextColumn::make('net_total')
                ->label('Amount')
                ->money('usd')
                ->sortable(),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'secondary' => 'PENDING',
                    'info' => 'DEPOSITED',
                    'warning' => 'PROCESSING',
                    'primary' => 'READY',
                    'success' => 'COMPLETED',
                    'danger' => 'CANCELLED',
                    'gray' => 'REFUNDED',
                ]),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Date')
                ->date()
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'PENDING' => 'Pending',
                    'DEPOSITED' => 'Deposited',
                    'PROCESSING' => 'Processing',
                    'READY' => 'Ready',
                    'COMPLETED' => 'Completed',
                    'CANCELLED' => 'Cancelled',
                    'REFUNDED' => 'Refunded',
                ]),

            MapIsFilter::make('map'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            GoToAction::make()
                ->zoom(15),
        ];
    }

    protected function getData(): array
    {
        $sales = $this->getTableQuery()->get();

        $data = [];

        foreach ($sales as $sale) {
            $data[] = [
                'location' => [
                    'lat' => (float) $sale->latitude,
                    'lng' => (float) $sale->longitude,
                ],
                'label' => $sale->sale_id,
                'id' => $sale->sale_id,
                // 'icon' => [
                //     'url' => 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                //     'type' => 'url',
                // ],
            ];
        }

        return $data;
    }

    protected function getOptions(): array
    {
        return [
            'center' => [
                'lat' => 11.5564,
                'lng' => 104.9282,
            ],
            'zoom' => 12,
        ];
    }

    public function markerAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('markerAction')
            ->label('Sale Details')
            ->modalContent(function (array $arguments) {
                $recordId = $arguments['model_id'] ?? $arguments['id'] ?? null;
                
                if (!$recordId) {
                    return view('filament.widgets.no-record');
                }
                
                // Sale model uses 'sale_id' as primary key
                $sale = Sale::with(['customer', 'agent'])->where('sale_id', $recordId)->first();
                
                if (!$sale) {
                    return view('filament.widgets.no-record');
                }
                
                return view('filament.widgets.sale-marker-details', ['sale' => $sale]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }
}