<?php

namespace App\Broadcasting;

use App\Models\Bot;
use App\Models\Host;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class BotChannel
{
    public function join(Authenticatable $authenticatable, string $id): bool
    {
        /** @var Bot $bot */
        $bot = Bot::find($id);
        if (is_null($bot)) {
            return false;
        }

        if ($authenticatable instanceof User) {
            return $authenticatable->id === $bot->creator_id;
        } else if ($authenticatable instanceof Host) {
            return $authenticatable->id === $bot->host_id;
        }
        return false;
    }
}
