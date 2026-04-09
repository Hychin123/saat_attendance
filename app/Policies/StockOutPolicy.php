<?php

namespace App\Policies;

use App\Models\StockOut;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockOutPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_outs')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, StockOut $stockOut): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'stock_outs')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'stock_outs')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, StockOut $stockOut): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'stock_outs')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, StockOut $stockOut): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'stock_outs')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, StockOut $stockOut): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, StockOut $stockOut): bool
    {
        return $user->isSuperAdmin();
    }
}
