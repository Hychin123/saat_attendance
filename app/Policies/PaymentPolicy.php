<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view_any', 'payment')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->role?->name === 'Accountant';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'payment')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->role?->name === 'Accountant'
            || $user->id === $payment->paid_by;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'payment')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Sales Manager'
            || $user->role?->name === 'Accountant'
            || $user->role?->name === 'Sales Agent';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('update', 'payment')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Accountant';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'payment')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Accountant';
    }
}
