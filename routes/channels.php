<?php

use App\Broadcasting\BotChannel;
use App\Broadcasting\HostChannel;
use App\Broadcasting\JobChannel;
use App\Broadcasting\UserChannel;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/


Broadcast::channel('bots.{id}', BotChannel::class, ['guards' => ['web', 'host']]);
Broadcast::channel('hosts.{id}', HostChannel::class, ['guards' => ['host']]);
Broadcast::channel('jobs.{id}', JobChannel::class, ['guards' => ['web', 'host']]);
Broadcast::channel('users.{id}', UserChannel::class, ['guards' => ['web']]);