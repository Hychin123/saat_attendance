<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'items')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Item $item): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'items')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'items')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Item $item): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'items')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'items')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Item $item): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Item $item): bool
    {
        return $user->isSuperAdmin();
    }
}
