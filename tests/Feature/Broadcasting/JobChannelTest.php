<?php

namespace Tests\Feature\Broadcasting;


use App\Models\Job;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class JobChannelTest extends BroadcastTestCase
{
    /** @test
     * @throws Exception
     */
    public function user_session_can_see_job_they_made()
    {
        $job = $this->job()->worker($this->bot()->create())->create();

        $this
            ->actingAs($this->mainUser)
            ->authPrivateChannel($job)
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test
     * @throws Exception
     */
    public function user_session_cannot_see_another_users_job()
    {
        $job = $this->job()
            ->creator($this->user()->create())
            ->worker($this->bot()->create())
            ->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->authPrivateChannel($job)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test
     * @throws Exception
     */
    public function user_session_cannot_see_unknown_job()
    {
        $id = $this->faker->uuid;

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->authPrivateChannel(Job::class, $id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test
     * @throws Exception
     */
    public function host_can_see_job_attached_to_bot_attached_to_it()
    {
        $bot = $this->bot()->host($this->mainHost)->create();
        $job = $this->job()->bot($bot)->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->authPrivateChannel($job)
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test
     * @throws Exception
     */
    public function host_cannot_see_job_that_has_no_bot()
    {
        $job = $this->job()->worker($this->bot()->create())->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authPrivateChannel($job)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}