<?php

namespace App\Policies;

use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockAdjustmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_adjustments')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_adjustments')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'stock_adjustments')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'stock_adjustments')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'stock_adjustments')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->isSuperAdmin();
    }
}
