<?php

namespace Tests\Feature\Host\Commands;

use App\Actions\AssignJobToBot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class GetBotsCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function hostCanAccessBotsAssignedToIt()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->host($this->mainHost)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => [
                    [
                        'data' => $bot->attributesToArray(),
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
    public function hostCanSeeWhenJobIsAvailable()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->driver($driverConfig)
            ->job_available()
            ->host($this->mainHost)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => [
                    [
                        'data' => $bot->attributesToArray(),
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
    public function hostCanSeeJobAssignedToBot()
    {
        $driverConfig = [
            'type' => 'dummy',
        ];

        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->mainHost)
            ->driver($driverConfig)
            ->create();

        $file = $this->file()->stl()->create();

        $job = $this->job()
            ->state(JobStatusEnum::QUEUED)
            ->file($file)
            ->worker($bot)
            ->create();

        app(AssignJobToBot::class)->execute($bot, $job);

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'ok' => true,
                'data' => [
                    [
                        'data' => $bot->attributesToArray(),
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
                            'current_job' => [
                                'id' => $bot->current_job_id,
                                'link' => route('api.jobs.view', $bot->current_job_id),
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function hostCanOnlySeeBotsAssignedToIt()
    {
        $otherHost = $this->host()->create();

        $driverConfig = [
            'type' => 'dummy',
        ];

        $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($otherHost)
            ->driver($driverConfig)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'GetBots',
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'ok' => true,
                'data' => [
                ],
            ]);
    }
}
