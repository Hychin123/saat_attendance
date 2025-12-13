<?php

namespace App\Policies;

use App\Models\MaterialAdjustment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaterialAdjustmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function view(User $user, MaterialAdjustment $materialAdjustment): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || ($user->role?->name === 'Smith' && $user->id === $materialAdjustment->user_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function update(User $user, MaterialAdjustment $materialAdjustment): bool
    {
        if ($user->isSuperAdmin() || $user->role?->name === 'HR Manager' || $user->role?->name === 'Warehouse Manager') {
            return true;
        }
        
        // Smith can only update their own pending records
        return $user->role?->name === 'Smith' 
            && $user->id === $materialAdjustment->user_id 
            && $materialAdjustment->status === 'pending';
    }

    public function delete(User $user, MaterialAdjustment $materialAdjustment): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, MaterialAdjustment $materialAdjustment): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, MaterialAdjustment $materialAdjustment): bool
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
