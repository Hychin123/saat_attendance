<?php

namespace App\Policies;

use App\Models\MachineWaterUsage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MachineWaterUsagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'machine_water_usage')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, MachineWaterUsage $machineWaterUsage): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'machine_water_usage')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'machine_water_usage')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, MachineWaterUsage $machineWaterUsage): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'machine_water_usage')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, MachineWaterUsage $machineWaterUsage): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'machine_water_usage')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, MachineWaterUsage $machineWaterUsage): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, MachineWaterUsage $machineWaterUsage): bool
    {
        return $user->isSuperAdmin();
    }
}
