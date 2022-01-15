<?php

namespace Tests\Feature\Api;

use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class FilesViewTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function can_see_my_file()
    {
        $file = $this->file()->stl()->create();

        $this
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/files/{$file->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $file->toArray(),
                'links' => [
                    'self' => [
                        'id' => $file->id,
                        'link' => route('api.files.view', $file->id),
                    ],
                    'uploader' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'download' => $file->url(),
                ],
            ]);
    }

    /** @test */
    public function can_see_my_file_using_explicit_scope()
    {
        $file = $this->file()->stl()->create();

        $this
            ->withTokenFromUser($this->mainUser, 'files')
            ->getJson("/api/files/{$file->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $file->toArray(),
                'links' => [
                    'self' => [
                        'id' => $file->id,
                        'link' => route('api.files.view', $file->id),
                    ],
                    'uploader' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'download' => $file->url(),
                ],
            ]);
    }

    /** @test */
    public function cannot_see_my_file_with_missing_scope()
    {
        $file = $this->file()->stl()->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser, [])
            ->getJson("/api/files/{$file->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function cannot_see_another_users_file()
    {
        $file = $this->file()->stl()->uploader($this->user()->create())->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromUser($this->mainUser)
            ->getJson("/api/files/{$file->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function host_can_see_file_used_in_job_it_is_running()
    {
        $bot = $this->bot()->host($this->mainHost)->create();
        $file = $this->file()->stl()->create();
        $this->job()->file($file)->bot($bot)->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/files/{$file->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => $file->toArray(),
                'links' => [
                    'self' => [
                        'id' => $file->id,
                        'link' => route('api.files.view', $file->id),
                    ],
                    'uploader' => [
                        'id' => $this->mainUser->id,
                        'link' => route('api.users.view', $this->mainUser->id),
                    ],
                    'download' => $file->url(),
                ],
            ]);
    }

    /** @test */
    public function host_cannot_see_file_used_in_job_it_is_not_running()
    {
        $bot = $this->bot()->host($this->mainHost)->create();
        $file = $this->file()->stl()->create();
        $this->job()->file($file)->worker($bot)->create();  # Worker is the bot, but it's not assigned just yet.

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/files/{$file->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function host_cannot_see_file_running_on_another_host()
    {
        $bot = $this->bot()->host($this->host()->create())->create();
        $file = $this->file()->stl()->create();
        $this->job()->file($file)->bot($bot)->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->getJson("/api/files/{$file->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
