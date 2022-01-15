<?php

namespace App\Policies;

use App\Models\Host;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    use UsesTokens;

    /**
     * Determine whether the user can view the model.
     *
     * @param User|Host $authed
     * @param User $model
     * @return bool
     * @throws AuthorizationException
     */
    public function view($authed, User $model): bool
    {
        $this->userNeedsScope($authed, 'users');

        if ($authed instanceof User) {
            return $authed->id === $model->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param User $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        //
    }
}
