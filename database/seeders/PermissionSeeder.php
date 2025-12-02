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
        $resources = ['users', 'roles', 'attendances', 'permissions'];
        $actions = [
            ['name' => 'view', 'label' => 'View'],
            ['name' => 'create', 'label' => 'Create'],
            ['name' => 'edit', 'label' => 'Edit'],
            ['name' => 'delete', 'label' => 'Delete'],
        ];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::create([
                    'name' => $action['name'],
                    'resource' => $resource,
                    'display_name' => $action['label'] . ' ' . ucfirst($resource),
                    'description' => 'Allows user to ' . $action['name'] . ' ' . $resource,
                ]);
            }
        }

        $this->command->info('Permissions created successfully!');
    }
}
