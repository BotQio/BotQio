<?php

namespace App\Policies;

use App\Models\Bot;
use App\Models\Host;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Laravel\Passport\HasApiTokens;

class BotPolicy
{
    use HandlesAuthorization;
    use UsesTokens;

    /**
     * Determine whether the user can view the bot.
     *
     * @param User|Host $authed
     * @param Bot $bot
     * @return bool
     * @throws AuthorizationException
     */
    public function view($authed, Bot $bot): bool
    {
        $this->userNeedsScope($authed, 'bots');

        if ($authed instanceof User) {
            return $bot->creator_id === $authed->id;
        }

        if ($authed instanceof Host) {
            return $bot->host_id === $authed->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create bots.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the bot.
     *
     * @param User $user
     * @param Bot $bot
     * @return mixed
     */
    public function update(User $user, Bot $bot)
    {
        return $bot->creator_id == $user->id;
    }

    /**
     * Determine whether the user can delete the bot.
     *
     * @param User $user
     * @param Bot $bot
     * @return mixed
     */
    public function delete(User $user, Bot $bot)
    {
        //
    }
}
