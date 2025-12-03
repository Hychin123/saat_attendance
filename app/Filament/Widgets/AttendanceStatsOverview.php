<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager';
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $totalUsers = User::count();
        
        // Users who checked in today
        $checkedInToday = Attendance::whereDate('date', $today)->count();
        
        // Users who haven't checked out yet
        $stillInOffice = Attendance::whereDate('date', $today)
            ->whereNull('time_out')
            ->count();
        
        // Users who checked out
        $checkedOut = Attendance::whereDate('date', $today)
            ->whereNotNull('time_out')
            ->count();
        
        // Absent users
        $absentToday = $totalUsers - $checkedInToday;
        
        return [
            Stat::make('Total Employees', $totalUsers)
                ->description('Registered in system')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            
            Stat::make('Present Today', $checkedInToday)
                ->description("{$absentToday} absent")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            
            Stat::make('Still in Office', $stillInOffice)
                ->description('Not checked out yet')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Checked Out', $checkedOut)
                ->description('Completed for today')
                ->descriptionIcon('heroicon-m-arrow-right-on-rectangle')
                ->color('info'),
        ];
    }
}
