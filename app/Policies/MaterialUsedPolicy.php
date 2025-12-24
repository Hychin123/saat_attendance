<?php

namespace App\Policies;

use App\Models\MaterialUsed;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaterialUsedPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'material_used')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function view(User $user, MaterialUsed $materialUsed): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'material_used')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || ($user->role?->name === 'Smith' && $user->id === $materialUsed->user_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'material_used')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function update(User $user, MaterialUsed $materialUsed): bool
    {
        if ($user->isSuperAdmin() || $user->role?->name === 'HR Manager' || $user->role?->name === 'Warehouse Manager') {
            return true;
        }
        
        // Smith can only update their own pending records
        return $user->role?->name === 'Smith' 
            && $user->id === $materialUsed->user_id 
            && $materialUsed->status === 'pending';
    }

    public function delete(User $user, MaterialUsed $materialUsed): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'material_used')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, MaterialUsed $materialUsed): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, MaterialUsed $materialUsed): bool
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
