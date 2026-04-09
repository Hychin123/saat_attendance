<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'code' => 'MS',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'grace_period_minutes' => 15,
                'minimum_work_hours' => 8,
                'is_active' => true,
                'is_overnight' => false,
                'description' => 'Standard morning shift from 8 AM to 4 PM',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'color' => '#3b82f6', // Blue
            ],
            [
                'name' => 'Afternoon Shift',
                'code' => 'AS',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'grace_period_minutes' => 15,
                'minimum_work_hours' => 8,
                'is_active' => true,
                'is_overnight' => false,
                'description' => 'Afternoon shift from 2 PM to 10 PM',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'color' => '#f59e0b', // Amber
            ],
            [
                'name' => 'Night Shift',
                'code' => 'NS',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'grace_period_minutes' => 15,
                'minimum_work_hours' => 8,
                'is_active' => true,
                'is_overnight' => true,
                'description' => 'Night shift from 10 PM to 6 AM (overnight)',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'color' => '#8b5cf6', // Purple
            ],
            [
                'name' => 'Day Shift',
                'code' => 'DS',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'grace_period_minutes' => 10,
                'minimum_work_hours' => 8,
                'is_active' => true,
                'is_overnight' => false,
                'description' => 'Regular day shift from 9 AM to 5 PM',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'color' => '#10b981', // Green
            ],
            [
                'name' => 'Weekend Shift',
                'code' => 'WS',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'grace_period_minutes' => 20,
                'minimum_work_hours' => 8,
                'is_active' => true,
                'is_overnight' => false,
                'description' => 'Weekend shift for Saturday and Sunday',
                'working_days' => ['saturday', 'sunday'],
                'color' => '#ef4444', // Red
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}
