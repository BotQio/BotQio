<?php

namespace App\Broadcasting;

use App\Models\User;

class UserChannel
{
    public function join(User $user, string $channelId): bool
    {
        return $user->id === $channelId;
    }
}
