<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class TodayAttendanceTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->whereDate('date', Carbon::today())
                    ->with(['user', 'role'])
                    ->latest('time_in')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('time_in')
                    ->label('Check In')
                    ->time()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('time_out')
                    ->label('Check Out')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('H:i:s') : '---')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->time_out ? 'Checked Out' : 'In Office';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Checked Out' => 'success',
                        'In Office' => 'warning',
                    }),
                
                Tables\Columns\TextColumn::make('work_hours')
                    ->label('Hours')
                    ->getStateUsing(function ($record) {
                        if ($record->time_out) {
                            $diff = $record->time_out->diffInMinutes($record->time_in);
                            $hours = floor($diff / 60);
                            $minutes = $diff % 60;
                            return "{$hours}h {$minutes}m";
                        }
                        return 'In progress';
                    }),
            ])
            ->heading("Today's Attendance - " . Carbon::today()->format('F d, Y'))
            ->defaultSort('time_in', 'desc');
    }
}
