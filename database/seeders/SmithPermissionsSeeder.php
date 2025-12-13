<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class SmithPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            'material_used',
            'material_adjustments',
            'smith_returns',
            'smith_stock_issues',
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

        $this->command->info('Smith management permissions created successfully!');
    }
}
