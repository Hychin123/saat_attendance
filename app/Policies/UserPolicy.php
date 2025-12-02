<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Check permission first, fallback to role-based
        if ($user->hasPermission('view', 'users')) {
            return true;
        }

        // Super admin can view all users
        // HR Manager can view all users
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Check permission first
        if ($user->hasPermission('view', 'users')) {
            return true;
        }

        // Super admin can view any user
        // Users can view their own profile
        // HR Manager can view any user
        return $user->isSuperAdmin() 
            || $user->id === $model->id 
            || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Check permission first
        if ($user->hasPermission('create', 'users')) {
            return true;
        }

        // Only super admin and HR Manager can create users
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Check permission first
        if ($user->hasPermission('edit', 'users')) {
            // HR with permission can't update super admins
            if (!$user->isSuperAdmin() && $model->isSuperAdmin()) {
                return false;
            }
            return true;
        }

        // Super admin can update any user
        // HR Manager can update any user except super admins
        // Users can update their own profile (except role and admin status)
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role?->name === 'HR Manager' && !$model->isSuperAdmin()) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Check permission first
        if ($user->hasPermission('delete', 'users')) {
            // Can't delete yourself
            return $user->id !== $model->id;
        }

        // Only super admin can delete users
        // Cannot delete yourself
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage super admin status.
     */
    public function manageSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRole(User $user): bool
    {
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }
}
