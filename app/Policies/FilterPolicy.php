<?php

namespace App\Policies;

use App\Models\Filter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilterPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Filter $filter): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Filter $filter): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Filter $filter): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'filters')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Filter $filter): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Filter $filter): bool
    {
        return $user->isSuperAdmin();
    }
}
