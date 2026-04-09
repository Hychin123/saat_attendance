<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'brands')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'brands')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'brands')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'brands')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'brands')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Brand $brand): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Brand $brand): bool
    {
        return $user->isSuperAdmin();
    }
}
