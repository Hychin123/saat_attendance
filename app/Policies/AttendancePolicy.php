<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Everyone can view attendance records
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Super admin can view all
        // HR Manager can view all
        // Users can view their own attendance
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create attendance
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Super admin can update any attendance
        // HR Manager can update any attendance
        // Users can only update their own attendance on the same day
        if ($user->isSuperAdmin() || $user->role?->name === 'HR Manager') {
            return true;
        }

        return $user->id === $attendance->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // Only super admin and HR Manager can delete attendance
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        // Only super admin can permanently delete
        return $user->isSuperAdmin();
    }
}
