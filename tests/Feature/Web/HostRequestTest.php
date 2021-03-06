<?php

namespace Tests\Feature\Web;

use App\Enums\HostRequestStatusEnum;
use App\Models\HostRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class HostRequestTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function anUnauthenticatedUserCannotViewHostRequest()
    {
        $host_request = $this->hostRequest()->create();

        $this
            ->withExceptionHandling()
            ->get("/hosts/requests/{$host_request->id}")
            ->assertRedirect('/login');
    }

    /** @test */
    public function aHostRequestCanBeViewed()
    {
        $host_request = $this->hostRequest()
            ->create();

        $this->assertEquals(null, $host_request->local_ip);
        $this->assertEquals(null, $host_request->hostname);

        $this
            ->actingAs($this->mainUser)
            ->get("/hosts/requests/{$host_request->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('host.request.show');
    }

    /** @test */
    public function anUnauthorizedUserCannotClaimHost()
    {
        $host_request = $this->hostRequest()->create();

        $newHostName = 'Test host';
        $this
            ->withExceptionHandling()
            ->post('/hosts', [
                'host_request_id' => $host_request->id,
                'name' => $newHostName,
            ])
            ->assertRedirect('/login');
    }

    /** @test */
    public function canCreateHostFromHostRequest()
    {
        $host_request = $this->hostRequest()->create();

        $newHostName = 'Test host';
        $this
            ->actingAs($this->mainUser)
            ->post('/hosts', [
                'host_request_id' => $host_request->id,
                'name' => $newHostName,
            ])
            ->assertRedirect('/home');

        $host_request->refresh();

        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
        $this->assertEquals($this->mainUser->id, $host_request->claimer_id);
        $this->assertEquals($newHostName, $host_request->hostname);
    }

    /** @test
     * @throws \App\Exceptions\HostAlreadyClaimed
     */
    public function aUserCannotClaimAnAlreadyClaimedHost()
    {
        $host_request = $this->hostRequest()->create();

        $otherUser = $this->user()->create();
        $otherUser->claim($host_request, 'Other User Test host');

        $this
            ->actingAs($this->mainUser)
            ->post('/hosts', [
                'host_request_id' => $host_request->id,
                'name' => 'My Test host',
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $host_request->refresh();
        $this->assertEquals($otherUser->id, $host_request->claimer_id);
        $this->assertEquals(HostRequestStatusEnum::CLAIMED, $host_request->status);
    }
}
