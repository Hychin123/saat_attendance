<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            // User & Attendance Management
            'users',
            'roles',
            'permissions',
            'attendances',
            'shifts',
            
            // Warehouse Management
            'warehouses',
            'items',
            'categories',
            'brands',
            'suppliers',
            'locations',
            'stocks',
            'stock_ins',
            'stock_outs',
            'stock_transfers',
            'stock_adjustments',
            'stock_movements',
        ];
        
        $actions = [
            ['name' => 'view', 'label' => 'View'],
            ['name' => 'create', 'label' => 'Create'],
            ['name' => 'edit', 'label' => 'Edit'],
            ['name' => 'delete', 'label' => 'Delete'],
        ];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::updateOrCreate(
                    [
                        'name' => $action['name'],
                        'resource' => $resource,
                    ],
                    [
                        'display_name' => $action['label'] . ' ' . ucfirst(str_replace('_', ' ', $resource)),
                        'description' => 'Allows user to ' . $action['name'] . ' ' . str_replace('_', ' ', $resource),
                    ]
                );
            }
        }

        $this->command->info('Permissions created successfully!');
    }
}
