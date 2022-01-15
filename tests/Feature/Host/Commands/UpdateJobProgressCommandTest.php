<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class UpdateJobProgressCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function defaultProgressIsZero()
    {
        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($this->bot()->create())
            ->create();

        $this->assertEquals(0.0, $job->progress);
    }

    /** @test */
    public function canUpdateProgress()
    {
        $file = $this->file()->gcode()->create();

        $bot = $this->bot()->host($this->mainHost)->create();
        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateJobProgress',
                'data' => [
                    'id' => $job->id,
                    'progress' => 50.0,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->attributesToArray(),
                'links' => [
                    'self' => [
                        'id' => $job->id,
                        'link' => route('api.jobs.view', $job->id),
                    ],
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'file' => [
                        'id' => $file->id,
                        'link' => route('api.files.view', $file->id),
                    ],
                    'worker' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                    'bot' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                ],
            ]);

        $job->refresh();

        $this->assertEquals(50.0, $job->progress);
    }

    /** @test */
    public function progressCannotBeSetToASmallerValueThanItIsCurrently()
    {
        $file = $this->file()->gcode()->create();

        $bot = $this->bot()->host($this->mainHost)->create();
        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateJobProgress',
                'data' => [
                    'id' => $job->id,
                    'progress' => 50.0,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->attributesToArray(),
                'links' => [
                    'self' => [
                        'id' => $job->id,
                        'link' => route('api.jobs.view', $job->id),
                    ],
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'file' => [
                        'id' => $file->id,
                        'link' => route('api.files.view', $file->id),
                    ],
                    'worker' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                    'bot' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                ],
            ]);

        $job->refresh();

        $this->assertEquals(50.0, $job->progress);

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateJobProgress',
                'data' => [
                    'id' => $job->id,
                    'progress' => 25.0,
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::jobPercentageCanOnlyIncrease()->toArray());

        $job->refresh();

        $this->assertEquals(50.0, $job->progress);
    }

    /** @test */
    public function precisionIsOnlyTwoDecimalPlaces()
    {
        $file = $this->file()->gcode()->create();

        $bot = $this->bot()->host($this->mainHost)->create();
        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateJobProgress',
                'data' => [
                    'id' => $job->id,
                    'progress' => 42.4242,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->attributesToArray(),
                'links' => [
                    'self' => [
                        'id' => $job->id,
                        'link' => route('api.jobs.view', $job->id),
                    ],
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'file' => [
                        'id' => $file->id,
                        'link' => route('api.files.view', $file->id),
                    ],
                    'worker' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                    'bot' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                ],
            ]);

        $job->refresh();

        $this->assertEquals(42.42, $job->progress);

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateJobProgress',
                'data' => [
                    'id' => $job->id,
                    'progress' => 42.4245,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->attributesToArray(),
                'links' => [
                    'self' => [
                        'id' => $job->id,
                        'link' => route('api.jobs.view', $job->id),
                    ],
                    'creator' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'file' => [
                        'id' => $file->id,
                        'link' => route('api.files.view', $file->id),
                    ],
                    'worker' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                    'bot' => [
                        'id' => $bot->id,
                        'link' => route('api.bots.view', $bot->id),
                    ],
                ],
            ]);

        $job->refresh();

        $this->assertEquals(42.42, $job->progress);
    }

    /** @test */
    public function jobIdMustBeSpecified()
    {
        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateJobProgress',
                'data' => [
                    'progress' => 50.0,
                ],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter('id')->toArray());
    }

    /** @test */
    public function progressMustBeSpecified()
    {
        $job = $this
            ->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($this->bot()->host($this->mainHost)->create())
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'UpdateJobProgress',
                'data' => [
                    'id' => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter('progress')->toArray());
    }
}
