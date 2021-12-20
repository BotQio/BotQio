<?php

namespace Tests\Feature\Broadcasting;


use App\Models\Bot;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class BotChannelTest extends BroadcastTestCase
{
    /** @test
     * @throws Exception
     */
    public function user_session_can_access_their_own_bot()
    {
        $bot = $this->bot()->create();

        $this
            ->actingAs($this->mainUser)
            ->authPrivateChannel($bot)
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test
     * @throws Exception
     */
    public function user_session_cannot_access_another_users_bot()
    {
        $bot = $this->bot()->creator($this->user()->create())->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->authPrivateChannel($bot)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test
     * @throws Exception
     */
    public function user_session_cannot_access_unknown_bot()
    {
        $id = $this->faker->uuid;

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->authPrivateChannel(Bot::class, $id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test
     * @throws Exception
     */
    public function host_can_access_a_bot_attached_to_them()
    {
        $bot = $this->bot()->host($this->mainHost)->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->authPrivateChannel($bot)
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test
     * @throws Exception
     */
    public function host_cannot_access_a_bot_attached_to_another_host()
    {
        $host = $this->host()->create();
        $bot = $this->bot()->host($host)->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authPrivateChannel($bot)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}