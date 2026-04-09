<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'locations')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Location $location): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'locations')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'locations')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Location $location): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'locations')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'locations')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Location $location): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Location $location): bool
    {
        return $user->isSuperAdmin();
    }
}
