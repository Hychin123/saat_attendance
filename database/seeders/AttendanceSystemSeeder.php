<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'name' => 'Software Developer',
                'department' => 'IT',
                'description' => 'Develops and maintains software applications',
            ],
            [
                'name' => 'HR Manager',
                'department' => 'Human Resources',
                'description' => 'Manages human resources and recruitment',
            ],
            [
                'name' => 'Marketing Specialist',
                'department' => 'Marketing',
                'description' => 'Handles marketing campaigns and strategies',
            ],
            [
                'name' => 'Sales Executive',
                'department' => 'Sales',
                'description' => 'Manages client relationships and sales',
            ],
            [
                'name' => 'Finance Officer',
                'department' => 'Finance',
                'description' => 'Manages financial operations and reporting',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        // Create sample users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => Hash::make('password'),
                'age' => 28,
                'school' => 'MIT',
                'role_id' => 1,
                'salary' => 75000,
                'kpa' => 'Full-stack Development',
                'phone' => '+1234567890',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => Hash::make('password'),
                'age' => 32,
                'school' => 'Harvard University',
                'role_id' => 2,
                'salary' => 65000,
                'kpa' => 'Employee Relations',
                'phone' => '+1234567891',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@example.com',
                'password' => Hash::make('password'),
                'age' => 25,
                'school' => 'Stanford University',
                'role_id' => 3,
                'salary' => 55000,
                'kpa' => 'Digital Marketing',
                'phone' => '+1234567892',
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@example.com',
                'password' => Hash::make('password'),
                'age' => 30,
                'school' => 'Yale University',
                'role_id' => 4,
                'salary' => 60000,
                'kpa' => 'Client Acquisition',
                'phone' => '+1234567893',
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@example.com',
                'password' => Hash::make('password'),
                'age' => 35,
                'school' => 'Princeton University',
                'role_id' => 5,
                'salary' => 70000,
                'kpa' => 'Financial Analysis',
                'phone' => '+1234567894',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'password' => Hash::make('password'),
                'age' => 27,
                'school' => 'Columbia University',
                'role_id' => 1,
                'salary' => 72000,
                'kpa' => 'Backend Development',
                'phone' => '+1234567895',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'age' => 40,
                'school' => 'Business School',
                'role_id' => 2,
                'salary' => 90000,
                'kpa' => 'System Administration',
                'phone' => '+1234567896',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Create sample attendance records for today
        $today = Carbon::today();
        $attendances = [
            [
                'user_id' => 1,
                'role_id' => 1,
                'date' => $today,
                'time_in' => $today->copy()->setTime(8, 30, 0),
                'time_out' => null, // Still in office
            ],
            [
                'user_id' => 2,
                'role_id' => 2,
                'date' => $today,
                'time_in' => $today->copy()->setTime(9, 0, 0),
                'time_out' => $today->copy()->setTime(17, 30, 0),
            ],
            [
                'user_id' => 3,
                'role_id' => 3,
                'date' => $today,
                'time_in' => $today->copy()->setTime(8, 45, 0),
                'time_out' => null,
            ],
            [
                'user_id' => 5,
                'role_id' => 5,
                'date' => $today,
                'time_in' => $today->copy()->setTime(8, 15, 0),
                'time_out' => $today->copy()->setTime(17, 0, 0),
            ],
            // User 4 and 6 are absent today
        ];

        foreach ($attendances as $attendanceData) {
            Attendance::create($attendanceData);
        }

        // Create some historical attendance records (last 7 days)
        for ($i = 1; $i <= 7; $i++) {
            $date = Carbon::today()->subDays($i);
            
            // Random attendance for each user
            foreach ([1, 2, 3, 4, 5, 6] as $userId) {
                if (rand(0, 10) > 2) { // 80% attendance rate
                    $user = User::find($userId);
                    $checkIn = $date->copy()->setTime(rand(7, 9), rand(0, 59), 0);
                    $checkOut = $date->copy()->setTime(rand(16, 18), rand(0, 59), 0);
                    
                    Attendance::create([
                        'user_id' => $userId,
                        'role_id' => $user->role_id,
                        'date' => $date,
                        'time_in' => $checkIn,
                        'time_out' => $checkOut,
                    ]);
                }
            }
        }

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
    }
}
