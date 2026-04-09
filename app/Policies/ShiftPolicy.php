<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShiftPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super admin and HR Manager can view all shifts
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shift $shift): bool
    {
        // Super admin and HR Manager can view any shift
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only super admin and HR Manager can create shifts
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shift $shift): bool
    {
        // Only super admin and HR Manager can update shifts
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shift $shift): bool
    {
        // Only super admin and HR Manager can delete shifts
        // Cannot delete shift if it has users assigned
        return ($user->isSuperAdmin() || $user->role?->name === 'HR Manager') 
            && $shift->users()->count() === 0;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shift $shift): bool
    {
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shift $shift): bool
    {
        return $user->isSuperAdmin() && $shift->users()->count() === 0;
    }
}
