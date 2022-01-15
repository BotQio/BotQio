<?php

namespace Tests\Feature\Api;

use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class JobsViewTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function user_can_see_their_jobs()
    {
        $bot = $this->bot()->create();
        $file = $this->file()->stl()->create();
        $job = $this->job()->file($file)->worker($bot)->create();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson('/api/jobs')
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    [
                        'data' => $job->refresh()->toArray(),
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
                            'bot' => null,
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function user_can_see_their_specific_job()
    {
        $bot = $this->bot()->create();
        $file = $this->file()->stl()->create();
        $job = $this->job()->file($file)->worker($bot)->create();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->toArray(),
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
                    'bot' => null,
                ],
            ]);
    }

    /** @test */
    public function worker_link_is_clusters_for_cluster_worker()
    {
        $cluster = $this->cluster()->create();
        $file = $this->file()->stl()->create();
        $job = $this->job()->file($file)->worker($cluster)->create();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->toArray(),
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
                        'id' => $cluster->id,
                        'link' => route('api.clusters.view', $cluster->id),
                    ],
                    'bot' => null,
                ],
            ]);
    }

    /** @test */
    public function user_cannot_see_another_users_job()
    {
        $bot = $this->bot()->create();
        $job = $this->job()->creator($this->user()->create())->worker($bot)->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function user_token_authorized_with_jobs_scope()
    {
        $bot = $this->bot()->create();
        $file = $this->file()->stl()->create();
        $job = $this->job()->file($file)->worker($bot)->create();

        $this
            ->withTokenFromUser($this->mainUser, 'jobs')
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->toArray(),
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
                    'bot' => null,
                ],
            ]);
    }

    /** @test */
    public function user_token_unauthorized_without_jobs_scope()
    {
        $bot = $this->bot()->create();
        $job = $this->job()->worker($bot)->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser, [])
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function host_token_can_see_job_if_it_can_see_associated_bot()
    {
        $bot = $this->bot()->host($this->mainHost)->create();
        $file = $this->file()->stl()->create();
        $job = $this->job()->file($file)->bot($bot)->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $job->refresh()->toArray(),
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
    }

    /** @test */
    public function host_cannot_see_job_if_it_cannot_see_bot()
    {
        $bot = $this->bot()->create();
        $job = $this->job()->bot($bot)->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser, [])
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function host_cannot_see_job_if_there_is_no_bot()
    {
        $bot = $this->bot()->create();
        $job = $this->job()->worker($bot)->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser, [])
            ->getJson("/api/jobs/{$job->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}