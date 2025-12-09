<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view_any', 'commission')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->role?->name === 'Accountant';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Commission $commission): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'commission')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->role?->name === 'Accountant'
            || $user->id === $commission->agent_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'commission')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Commission $commission): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('update', 'commission')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Accountant';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Commission $commission): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'commission')
            || $user->role?->name === 'HR Manager';
    }
}
