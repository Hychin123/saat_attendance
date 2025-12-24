<?php

namespace App\Policies;

use App\Models\FilterReplacement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilterReplacementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'filter_replacements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, FilterReplacement $filterReplacement): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'filter_replacements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'filter_replacements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, FilterReplacement $filterReplacement): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'filter_replacements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, FilterReplacement $filterReplacement): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'filter_replacements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, FilterReplacement $filterReplacement): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, FilterReplacement $filterReplacement): bool
    {
        return $user->isSuperAdmin();
    }
}
