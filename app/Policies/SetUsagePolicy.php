<?php

namespace App\Policies;

use App\Models\SetUsage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SetUsagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'set_usages')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, SetUsage $setUsage): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'set_usages')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->id === $setUsage->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'set_usages')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, SetUsage $setUsage): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'set_usages')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, SetUsage $setUsage): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'set_usages')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, SetUsage $setUsage): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, SetUsage $setUsage): bool
    {
        return $user->isSuperAdmin();
    }
}
