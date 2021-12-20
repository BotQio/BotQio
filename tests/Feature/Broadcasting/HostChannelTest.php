<?php

namespace Tests\Feature\Broadcasting;


use App\Models\Host;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class HostChannelTest extends BroadcastTestCase
{
    /** @test
     * @throws Exception
     */
    public function user_cannot_see_host_channel_even_if_belongs_to_them()
    {
        $host = $this->host()->create();

        $this
            ->withExceptionHandling()
            ->actingAs($this->mainUser)
            ->authPrivateChannel($host)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test
     * @throws Exception
     */
    public function host_can_see_their_channel()
    {
        $this
            ->withTokenFromHost($this->mainHost)
            ->authPrivateChannel($this->mainHost)
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test
     * @throws Exception
     */
    public function host_cannot_see_another_host_channel()
    {
        $host = $this->host()->create();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authPrivateChannel($host)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test
     * @throws Exception
     */
    public function host_cannot_see_unknown_host()
    {
        $id = $this->faker->uuid;

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->authPrivateChannel(Host::class, $id)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}