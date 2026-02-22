<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->icon('heroicon-o-calendar'),
            
            'today' => Tab::make('Today')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('date', today()))
                ->badge(fn () => AttendanceResource::getModel()::whereDate('date', today())->count()),
            
            'this_week' => Tab::make('This Week')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('date', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]))
                ->badge(fn () => AttendanceResource::getModel()::whereBetween('date', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->count()),
            
            'this_month' => Tab::make('This Month')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                )
                ->badge(fn () => AttendanceResource::getModel()::
                    whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count()),
            
            'last_month' => Tab::make('Last Month')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('date', now()->subMonth()->month)
                    ->whereYear('date', now()->subMonth()->year)
                )
                ->badge(fn () => AttendanceResource::getModel()::
                    whereMonth('date', now()->subMonth()->month)
                    ->whereYear('date', now()->subMonth()->year)
                    ->count()),
            
            'this_year' => Tab::make('This Year')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereYear('date', now()->year))
                ->badge(fn () => AttendanceResource::getModel()::whereYear('date', now()->year)->count()),
        ];
    }
}
