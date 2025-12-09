<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view_any', 'sale')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->role?->name === 'Sales Agent';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'sale')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->id === $sale->agent_id
            || $user->id === $sale->customer_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'sale')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->role?->name === 'Sales Agent';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('update', 'sale')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'sale')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sale $sale): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('restore', 'sale')
            || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sale $sale): bool
    {
        return $user->isSuperAdmin();
    }
}
