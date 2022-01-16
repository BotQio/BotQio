<?php

namespace Tests\Feature\Api;

use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class BotsViewTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function botsIndex()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->host($this->mainHost)
            ->create();
        $bot->refresh(); // Forces all keys to show in array

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson('/api/bots')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => [
                    [
                        'data' => $bot->attributesToArray(),  // TODO Actually verify what this spits out in another test
                        'links' => [
                            'self' => [
                                'id' => $bot->id,
                                'link' => route('api.bots.view', $bot->id),
                            ],
                            'creator' => [
                                'id' => $this->mainUser->id,
                                'link' => route('api.users.view', $this->mainUser->id),
                            ],
                            'host' => [
                                'id' => $this->mainHost->id,
                                'link' => route('api.hosts.view', $this->mainHost->id),
                            ],
                            'cluster' => [
                                'id' => $bot->cluster_id,
                                'link' => route('api.clusters.view', $bot->cluster_id),
                            ],
                            'current_job' => null,
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function botsThatAreNotMineAreNotVisibleInIndex()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->create();

        $other_user = $this->user()->create();
        $this->bot()
            ->creator($other_user)
            ->create();
        $bot->refresh();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson('/api/bots')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => [
                    [
                        'data' => $bot->attributesToArray(),  // TODO Actually verify what this spits out in another test
                        'links' => [
                            'self' => [
                                'id' => $bot->id,
                                'link' => route('api.bots.view', $bot->id),
                            ],
                            'creator' => [
                                'id' => $this->mainUser->id,
                                'link' => route('api.users.view', $this->mainUser->id),
                            ],
                            'host' => null,
                            'cluster' => [
                                'id' => $bot->cluster_id,
                                'link' => route('api.clusters.view', $bot->cluster_id),
                            ],
                            'current_job' => null,
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function canSeeMyOwnBot()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->create();
        $bot->refresh();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => $bot->attributesToArray(),  // TODO Actually verify what this spits out in another test
                'links' => [
                    'self' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'host' => null,
                    'cluster' => [
                        'id' => $bot->cluster_id,
                        'link' => route('api.clusters.view', $bot->cluster_id),
                    ],
                    'current_job' => null,
                ],
            ]);
    }

    /** @test */
    public function canSeeMyOwnBotGivenExplicitScope()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->create();
        $bot->refresh();

        $this
            ->withTokenFromUser($this->mainUser, 'bots')
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => $bot->attributesToArray(),  // TODO Actually verify what this spits out in another test
                'links' => [
                    'self' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'host' => null,
                    'cluster' => [
                        'id' => $bot->cluster_id,
                        'link' => route('api.clusters.view', $bot->cluster_id),
                    ],
                    'current_job' => null,
                ],
            ]);
    }

    /** @test */
    public function cannotSeeOtherBot()
    {
        $other_user = $this->user()->create();
        $other_bot = $this->bot()
            ->creator($other_user)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/bots/{$other_bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function cannotSeeMyBotIfMissingCorrectScope()
    {
        $bot = $this->bot()->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser, [])
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function hostCanSeeTheirBots()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->host($this->mainHost)
            ->create();
        $bot->refresh(); // Forces all keys to show in array

        $this
            ->withTokenFromHost($this->mainHost)
            ->getJson('/api/bots')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => [
                    [
                        'data' => $bot->attributesToArray(),  // TODO Actually verify what this spits out in another test
                        'links' => [
                            'self' => [
                                'id' => $bot->id,
                                'link' => route('api.bots.view', $bot->id),
                            ],
                            'creator' => [
                                'id' => $this->mainUser->id,
                                'link' => route('api.users.view', $this->mainUser->id),
                            ],
                            'host' => [
                                'id' => $this->mainHost->id,
                                'link' => route('api.hosts.view', $this->mainHost->id),
                            ],
                            'cluster' => [
                                'id' => $bot->cluster_id,
                                'link' => route('api.clusters.view', $bot->cluster_id),
                            ],
                            'current_job' => null,
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function hostCanSeeSpecificBot()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->host($this->mainHost)
            ->create();
        $bot->refresh(); // Forces all keys to show in array

        $this
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => $bot->attributesToArray(),  // TODO Actually verify what this spits out in another test
                'links' => [
                    'self' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'host' => [
                        'id' => $this->mainHost->id,
                        'link' => route('api.hosts.view', $this->mainHost->id),
                    ],
                    'cluster' => [
                        'id' => $bot->cluster_id,
                        'link' => route('api.clusters.view', $bot->cluster_id),
                    ],
                    'current_job' => null,
                ],
            ]);
    }

    /** @test */
    public function hostCannotSeeBotThatIsNotTheirs()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->create();
        $bot->refresh(); // Forces all keys to show in array

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
