<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class StartJobCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson('/host', [
                'command' => 'StartJob',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function aHostCanUpdateJobStatusFromAssignedToInProgress()
    {
        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->host($this->mainHost)
            ->create();

        $file = $this->file()->gcode()->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->bot($bot)
            ->file($file)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'StartJob',
                'data' => [
                    'id' => $job->id,
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

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(JobStatusEnum::IN_PROGRESS, $job->status);
        $this->assertEquals(BotStatusEnum::WORKING, $bot->status);
    }

    /** @test
     * @dataProvider jobStates
     * @param $jobState
     */
    public function aHostCanNotGoFromANonAssignedStateToInProgress($jobState)
    {
        $this->exceptStatus(JobStatusEnum::ASSIGNED);

        $this->withoutJobs();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->host($this->mainHost)
            ->create();

        $job = $this->job()
            ->state($jobState)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'StartJob',
                'data' => [
                    'id' => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::jobIsNotAssigned()->toArray());
    }

    /** @test */
    public function aHostCanNotUpdateAJobThatIsNotBeingWorkedOnByItsOwnBots()
    {
        $this->withoutJobs();

        $host = $this->host()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::JOB_ASSIGNED)
            ->host($host)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::ASSIGNED)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'StartJob',
                'data' => [
                    'id' => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertExactJson(HostErrors::jobIsNotAssignedToThisHost()->toArray());
    }
}
