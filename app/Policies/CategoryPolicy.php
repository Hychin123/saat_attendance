<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'categories')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function view(User $user, Category $category): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('view', 'categories')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('create', 'categories')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('edit', 'categories')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isSuperAdmin() 
            || $user->hasPermission('delete', 'categories')
            || $user->role?->name === 'HR Manager'
            || $user->role?->name === 'Warehouse Manager';
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->isSuperAdmin();
    }
}
