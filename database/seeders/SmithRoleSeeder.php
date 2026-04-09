<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SmithRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Smith role
        $smithRole = Role::firstOrCreate(
            ['name' => 'Smith'],
            [
                'department' => 'Production',
                'description' => 'Handles material usage, adjustments, returns, and stock issues',
            ]
        );

        // Create Warehouse Manager role if it doesn't exist
        $warehouseManagerRole = Role::firstOrCreate(
            ['name' => 'Warehouse Manager'],
            [
                'department' => 'Operations',
                'description' => 'Manages warehouse operations, inventory, and approves smith requests',
            ]
        );

        // Create a sample Smith user (optional)
        User::firstOrCreate(
            ['email' => 'smith.user@example.com'],
            [
                'name' => 'John Smith',
                'password' => Hash::make('password'),
                'age' => 35,
                'school' => 'Technical Institute',
                'role_id' => $smithRole->id,
                'salary' => 45000,
                'kpa' => 'Material Management',
                'phone' => '+1234567899',
            ]
        );

        // Create a sample Warehouse Manager user (optional)
        User::firstOrCreate(
            ['email' => 'warehouse.manager@example.com'],
            [
                'name' => 'Manager Williams',
                'password' => Hash::make('password'),
                'age' => 40,
                'school' => 'Business School',
                'role_id' => $warehouseManagerRole->id,
                'salary' => 65000,
                'kpa' => 'Warehouse Operations',
                'phone' => '+1234567898',
            ]
        );
    }
}
