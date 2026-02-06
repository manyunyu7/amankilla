<?php

namespace App\Policies;

use App\Models\Universe;
use App\Models\User;

class UniversePolicy
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
    public function view(User $user, Universe $universe): bool
    {
        return $user->id === $universe->user_id || $universe->is_public;
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
    public function update(User $user, Universe $universe): bool
    {
        return $user->id === $universe->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Universe $universe): bool
    {
        return $user->id === $universe->user_id;
    }

    /**
     * Determine whether the user can fork the universe.
     */
    public function fork(User $user, Universe $universe): bool
    {
        return $universe->is_public && $universe->allow_fork && $user->id !== $universe->user_id;
    }
}
