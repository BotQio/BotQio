<?php

namespace Tests\Feature\Host\Commands;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use App\Events\JobFinished;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class FinishJobCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson('/host', [
                'command' => 'FinishJob',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function aHostCanUpdateJobStatusFromInProgressToQualityCheck()
    {
        $this->withoutJobs();

        $this->fakesEvents(JobFinished::class);

        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->host($this->mainHost)
            ->create();

        $file = $this->file()->gcode()->create();

        $job = $this->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->file($file)
            ->create();

        $response = $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'FinishJob',
                'data' => [
                    'id' => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_OK);

        $job->refresh();

        $response
            ->assertJson([
                'ok' => true,
                'data' => $job->attributesToArray(),
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

        $this->assertEquals(JobStatusEnum::QUALITY_CHECK, $job->status);
        $this->assertEquals(BotStatusEnum::WAITING, $bot->status);

        $this->assertDispatched(JobFinished::class)
            ->inspect(function ($event) use ($job) {
                /* @var JobFinished $event */
                $this->assertEquals($job->id, $event->job->id);
            });
    }

    /** @test
     * @dataProvider jobStates
     * @param $jobState
     */
    public function aHostCanNotGoFromANonInProgressStateToQualityCheck($jobState)
    {
        $this->exceptStatus(JobStatusEnum::IN_PROGRESS);

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
                'command' => 'FinishJob',
                'data' => [
                    'id' => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::jobIsNotInProgress()->toArray());
    }

    /** @test */
    public function aHostCanNotUpdateAJobThatIsNotBeingWorkedOnByItsOwnBots()
    {
        $this->withoutJobs();

        $host = $this->host()->create();

        $bot = $this->bot()
            ->state(BotStatusEnum::WORKING)
            ->host($host)
            ->create();

        $job = $this->job()
            ->state(JobStatusEnum::IN_PROGRESS)
            ->bot($bot)
            ->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'FinishJob',
                'data' => [
                    'id' => $job->id,
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertExactJson(HostErrors::jobIsNotAssignedToThisHost()->toArray());
    }
}
