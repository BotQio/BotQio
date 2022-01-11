<?php

namespace App\Policies;

use App\Models\Bot;
use App\Models\Host;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Laravel\Passport\HasApiTokens;

class BotPolicy
{
    use HandlesAuthorization;

    /**
     * @param User|Host $hasApiTokens
     * @return bool
     */
    protected function usingToken($hasApiTokens): bool
    {
        return !is_null($hasApiTokens->token());
    }

    /**
     * Determine whether the user can view the bot.
     *
     * @param User|Host $authed
     * @param Bot $bot
     * @return bool|Response
     */
    public function view($authed, Bot $bot)
    {
        if ($authed instanceof User) {
            if ($this->usingToken($authed) && !$authed->tokenCan('bots')) {
                return $this->deny("Token does not have required scope \"bots\"");
            }

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
