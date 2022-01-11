<?php

namespace App\Policies;

use App\Models\Host;
use App\Models\Job;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPolicy
{
    use HandlesAuthorization;
    use UsesTokens;

    /**
     * Determine whether the user can view the model.
     *
     * @param User|Host $authed
     * @param Job $job
     * @return bool
     * @throws AuthorizationException
     */
    public function view($authed, Job $job): bool
    {
        $this->userNeedsScope($authed, 'jobs');

        if ($authed instanceof User) {
            return $job->creator_id === $authed->id;
        }

        if ($authed instanceof Host) {
            if (is_null($job->bot)) {
                return false;
            }
            return $authed->can('view', $job->bot);
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
     * @param Job $job
     * @return mixed
     */
    public function update(User $user, Job $job)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Job $job
     * @return mixed
     */
    public function delete(User $user, Job $job)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Job $job
     * @return mixed
     */
    public function restore(User $user, Job $job)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Job $job
     * @return mixed
     */
    public function forceDelete(User $user, Job $job)
    {
        //
    }
}
