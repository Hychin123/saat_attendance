<?php

namespace App\Policies;

use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockMovementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_movements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, StockMovement $stockMovement): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_movements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'stock_movements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, StockMovement $stockMovement): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'stock_movements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, StockMovement $stockMovement): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'stock_movements')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, StockMovement $stockMovement): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, StockMovement $stockMovement): bool
    {
        return $user->isSuperAdmin();
    }
}
