<?php

namespace App\Policies;

use App\Models\MachineFilter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MachineFilterPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'machine_filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, MachineFilter $machineFilter): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'machine_filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'machine_filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, MachineFilter $machineFilter): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'machine_filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, MachineFilter $machineFilter): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'machine_filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, MachineFilter $machineFilter): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, MachineFilter $machineFilter): bool
    {
        return $user->isSuperAdmin();
    }
}
