<?php

namespace Tests\Feature\Broadcasting;


use App\Models\User;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserChannelTest extends BroadcastTestCase
{
    /** @test
     * @throws Exception
     */
    public function user_session_can_access_their_channel()
    {
        $this
            ->actingAs($this->mainUser)
            ->authPrivateChannel($this->mainUser)
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test
     * @throws Exception
     */
    public function user_session_cannot_see_another_user_channel()
    {
        $otherUser = $this->user()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->authPrivateChannel($otherUser)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test
     * @throws Exception
     */
    public function user_session_cannot_request_unknown_user()
    {
        $id = $this->faker->uuid;

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->authPrivateChannel(User::class, $id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}