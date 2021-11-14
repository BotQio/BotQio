<?php

namespace Tests\Feature\Host\Commands;

use Illuminate\Support\Facades\Config;
use Tests\Helpers\HostCommandHelper;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class InfoCommandTest extends TestCase
{
    use PassportHelper;
    use HostCommandHelper;

    protected $ignoreHostAuth = true;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('app.url', 'https://example.com/');
    }

    /** @test */
    public function info_command_returns_useful_server_info()
    {
        $this->command('Info')
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'websocket' => [
                        'url' => 'wss://example.com/ws/app/BotQio-key',
                    ],
                ],
            ]);
    }

    /** @test */
    public function websocket_returns_ws_for_http()
    {
        Config::set('app.url', 'http://example.com/');

        $this->command('Info')
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'websocket' => [
                        'url' => 'ws://example.com/ws/app/BotQio-key',
                    ],
                ],
            ]);
    }
}
