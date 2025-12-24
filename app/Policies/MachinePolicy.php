<?php

namespace App\Policies;

use App\Models\Machine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MachinePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'machines')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Machine $machine): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'machines')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'machines')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Machine $machine): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'machines')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Machine $machine): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'machines')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Machine $machine): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Machine $machine): bool
    {
        return $user->isSuperAdmin();
    }
}
