<?php

namespace App\Policies;

use App\Models\SmithReturn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmithReturnPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function view(User $user, SmithReturn $smithReturn): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || ($user->role?->name === 'Smith' && $user->id === $smithReturn->user_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function update(User $user, SmithReturn $smithReturn): bool
    {
        if ($user->isSuperAdmin() || $user->role?->name === 'HR Manager' || $user->role?->name === 'Warehouse Manager') {
            return true;
        }
        
        // Smith can only update their own pending records
        return $user->role?->name === 'Smith' 
            && $user->id === $smithReturn->user_id 
            && $smithReturn->status === 'pending';
    }

    public function delete(User $user, SmithReturn $smithReturn): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, SmithReturn $smithReturn): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, SmithReturn $smithReturn): bool
    {
        return $user->isSuperAdmin();
    }

    public function approve(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }
}
