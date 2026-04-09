<?php

namespace App\Policies;

use App\Models\StockIn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockInPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_ins')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, StockIn $stockIn): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_ins')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'stock_ins')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, StockIn $stockIn): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'stock_ins')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, StockIn $stockIn): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'stock_ins')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, StockIn $stockIn): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, StockIn $stockIn): bool
    {
        return $user->isSuperAdmin();
    }
}
