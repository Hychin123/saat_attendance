<?php

namespace App\Policies;

use App\Models\SmithStockIssue;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmithStockIssuePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function view(User $user, SmithStockIssue $smithStockIssue): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || ($user->role?->name === 'Smith' && $user->id === $smithStockIssue->user_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager'
            || $user->role?->name === 'Smith';
    }

    public function update(User $user, SmithStockIssue $smithStockIssue): bool
    {
        if ($user->isSuperAdmin() || $user->role?->name === 'HR Manager' || $user->role?->name === 'Warehouse Manager') {
            return true;
        }
        
        // Smith can only update their own pending records
        return $user->role?->name === 'Smith' 
            && $user->id === $smithStockIssue->user_id 
            && $smithStockIssue->status === 'pending';
    }

    public function delete(User $user, SmithStockIssue $smithStockIssue): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, SmithStockIssue $smithStockIssue): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, SmithStockIssue $smithStockIssue): bool
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
