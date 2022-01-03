<?php

namespace Tests\Unit\Events;


use App\Events\BotUpdated;
use Tests\TestCase;

class BotUpdatedTest extends TestCase
{
    /** @test */
    public function broadcastChannelsWithoutHost()
    {
        $bot = $this->bot()->create();

        $event = new BotUpdated($bot);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-bots.' . $bot->id,
            ],
            $event->broadcastOn()
        );
    }

    /** @test */
    public function broadcastChannelsWithHost()
    {
        $bot = $this->bot()->host($this->mainHost)->create();

        $event = new BotUpdated($bot);

        $this->assertEquals(
            [
                'private-users.' . $this->mainUser->id,
                'private-bots.' . $bot->id,
                'private-hosts.' . $this->mainHost->id,
            ],
            $event->broadcastOn()
        );
    }
}