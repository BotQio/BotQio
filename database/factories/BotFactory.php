<?php

use Faker\Generator as Faker;
use App\Bot;
use App\Job;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Bot::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
        'seen_at' => $faker->dateTime,
        'type' => '3d_printer',
    ];
});

$factory->state(Bot::class, BotStatusEnum::OFFLINE, function () {
    return [
        'status' => BotStatusEnum::OFFLINE,
    ];
});

$factory->state(Bot::class, BotStatusEnum::IDLE, function () {
    return [
        'status' => BotStatusEnum::IDLE,
    ];
});

$factory->state(Bot::class, BotStatusEnum::WORKING, function () {
    return [
        'status' => BotStatusEnum::WORKING,
    ];
});