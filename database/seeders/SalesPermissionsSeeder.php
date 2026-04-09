<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class SalesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sales permissions with display names
        $salesPermissions = [
            // Sale permissions
            ['name' => 'view_any_sale', 'resource' => 'sale', 'display_name' => 'View Any Sale', 'description' => 'Can view all sales'],
            ['name' => 'view_sale', 'resource' => 'sale', 'display_name' => 'View Sale', 'description' => 'Can view sale details'],
            ['name' => 'create_sale', 'resource' => 'sale', 'display_name' => 'Create Sale', 'description' => 'Can create new sales'],
            ['name' => 'update_sale', 'resource' => 'sale', 'display_name' => 'Update Sale', 'description' => 'Can update sales'],
            ['name' => 'delete_sale', 'resource' => 'sale', 'display_name' => 'Delete Sale', 'description' => 'Can delete sales'],
            ['name' => 'restore_sale', 'resource' => 'sale', 'display_name' => 'Restore Sale', 'description' => 'Can restore deleted sales'],
            ['name' => 'force_delete_sale', 'resource' => 'sale', 'display_name' => 'Force Delete Sale', 'description' => 'Can permanently delete sales'],
            
            // Payment permissions
            ['name' => 'view_any_payment', 'resource' => 'payment', 'display_name' => 'View Any Payment', 'description' => 'Can view all payments'],
            ['name' => 'view_payment', 'resource' => 'payment', 'display_name' => 'View Payment', 'description' => 'Can view payment details'],
            ['name' => 'create_payment', 'resource' => 'payment', 'display_name' => 'Create Payment', 'description' => 'Can create new payments'],
            ['name' => 'update_payment', 'resource' => 'payment', 'display_name' => 'Update Payment', 'description' => 'Can update payments'],
            ['name' => 'delete_payment', 'resource' => 'payment', 'display_name' => 'Delete Payment', 'description' => 'Can delete payments'],
            
            // Commission permissions
            ['name' => 'view_any_commission', 'resource' => 'commission', 'display_name' => 'View Any Commission', 'description' => 'Can view all commissions'],
            ['name' => 'view_commission', 'resource' => 'commission', 'display_name' => 'View Commission', 'description' => 'Can view commission details'],
            ['name' => 'create_commission', 'resource' => 'commission', 'display_name' => 'Create Commission', 'description' => 'Can create new commissions'],
            ['name' => 'update_commission', 'resource' => 'commission', 'display_name' => 'Update Commission', 'description' => 'Can update commissions'],
            ['name' => 'delete_commission', 'resource' => 'commission', 'display_name' => 'Delete Commission', 'description' => 'Can delete commissions'],
        ];

        $createdPermissions = [];
        
        foreach ($salesPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'resource' => $permissionData['resource'],
                    'display_name' => $permissionData['display_name'],
                    'description' => $permissionData['description'],
                ]
            );
            $createdPermissions[] = $permission->id;
        }

        // Assign all permissions to admin role if exists
        $adminRole = Role::where('name', 'admin')->orWhere('name', 'Admin')->first();
        if ($adminRole) {
            // Get existing permission IDs for this role
            $existingPermissionIds = DB::table('role_permissions')
                ->where('role_id', $adminRole->id)
                ->pluck('permission_id')
                ->toArray();
            
            // Add new permissions
            $newPermissions = array_diff($createdPermissions, $existingPermissionIds);
            
            foreach ($newPermissions as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $this->command->info('Sales permissions assigned to admin role successfully!');
        } else {
            $this->command->warn('Admin role not found. Permissions created but not assigned to any role.');
        }

        $this->command->info('Sales permissions created successfully!');
        $this->command->info('Total permissions created: ' . count($salesPermissions));
    }
}
