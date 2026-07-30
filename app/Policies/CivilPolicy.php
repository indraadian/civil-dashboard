<?php

namespace App\Policies;

use App\Models\Civil;
use App\Models\User;

class CivilPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Civil $civil): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Civil::query()->forUser($user)->where('id', $civil->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Civil $civil): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return Civil::query()->forUser($user)->where('id', $civil->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Civil $civil): bool
    {
        return $user->isAdmin();
    }
}
