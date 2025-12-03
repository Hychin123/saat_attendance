<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class AbsentEmployeesTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager';
    }

    public function table(Table $table): Table
    {
        // Get IDs of users who checked in today
        $presentUserIds = Attendance::whereDate('date', Carbon::today())
            ->pluck('user_id')
            ->toArray();    

        return $table
            ->query(
                User::query()
                    ->whereNotIn('id', $presentUserIds)
                    ->with('role')
                    ->orderBy('name')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->color('danger')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->default('Absent')
                    ->color('danger'),
            ])
            ->heading("Absent Today - " . Carbon::today()->format('F d, Y'))
            ->emptyStateHeading('Everyone is present!')
            ->emptyStateDescription('All employees have checked in today.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
