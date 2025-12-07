<?php

namespace App\Policies;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Stock $stock): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Stock $stock): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Stock $stock): bool
    {
        return $user->isSuperAdmin() 
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Stock $stock): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Stock $stock): bool
    {
        return $user->isSuperAdmin();
    }
}
