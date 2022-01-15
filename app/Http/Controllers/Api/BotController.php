<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BotCollection;
use App\Http\Resources\BotResource;
use App\Models\Bot;
use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
{
    public function index()
    {
        /** @var User|Host $user */
        $user = Auth::user();

        $bots = $user->bots()->get();

        return new BotCollection($bots);
    }

    public function show(Bot $bot)
    {
        return new BotResource($bot);
    }
}
