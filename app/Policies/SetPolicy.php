<?php

namespace App\Policies;

use App\Models\Set;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'sets')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Set $set): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'sets')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'sets')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Set $set): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'sets')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Set $set): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'sets')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Set $set): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Set $set): bool
    {
        return $user->isSuperAdmin();
    }
}
