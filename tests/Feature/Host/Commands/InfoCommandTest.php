<?php

namespace Tests\Feature\Host\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\Helpers\HostCommandHelper;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class InfoCommandTest extends TestCase
{
    use PassportHelper;
    use HostCommandHelper;

    protected function setUrl($url)
    {
        Config::set('app.url', $url);
        url()->forceRootUrl($url);
        $scheme = Str::startsWith($url, 'http://') ? 'http' : 'https';
        url()->forceScheme($scheme);
    }

    /** @test */
    public function info_command_returns_useful_server_info()
    {
        $this->setUrl('https://example.com/');

        $this->command('Info')
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'websocket' => [
                        'url' => 'wss://example.com/ws/app/BotQio-key',
                        'auth' => 'https://example.com/broadcasting/auth',
                    ],
                ],
            ]);
    }

    /** @test */
    public function websocket_returns_ws_for_http()
    {
        $this->setUrl('http://example.com/');

        $this->command('Info')
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'websocket' => [
                        'url' => 'ws://example.com/ws/app/BotQio-key',
                        'auth' => 'http://example.com/broadcasting/auth',
                    ],
                ],
            ]);
    }
}
