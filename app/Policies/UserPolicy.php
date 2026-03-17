<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super Admin and Admin can view users
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Super Admin can view anyone
        if ($user->isSuperAdmin()) {
            return true;
        }
        // Admin can view Cashiers and self
        if ($user->isAdmin()) {
            return $model->isCashier() || $user->id === $model->id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super Admin can create Admins and Cashiers
        if ($user->isSuperAdmin()) {
            return true;
        }
        // Admin can create Cashiers
        if ($user->isAdmin()) {
            return true; // Assumption: Admins can create Cashiers
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Super Admin can update anyone
        if ($user->isSuperAdmin()) {
            return true;
        }
        // Admin can update Cashiers and self
        if ($user->isAdmin()) {
            return $model->isCashier() || $user->id === $model->id;
        }
        // Cashier can only update self
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Super Admin can delete Admins and Cashiers (but not self if only one Super Admin? For now, allow)
        if ($user->isSuperAdmin()) {
            return $model->id !== $user->id; // Prevent deleting self
        }
        // Admin can delete Cashiers
        if ($user->isAdmin()) {
            return $model->isCashier();
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
