<?php

namespace Tests\Feature\Host\Commands;

use App\Errors\HostErrors;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser as JwtParser;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class RefreshAccessTokenCommandTest extends TestCase
{
    use PassportHelper;

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->postJson('/host', [
                'command' => 'RefreshAccessToken',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function canRefreshSuccessfully()
    {
        $jwt = app(JwtParser::class);

        $original_token = $this->mainHost->getAccessToken();

        $first_expire_time = $original_token->getExpiryDateTime()->getTimestamp();

        Carbon::setTestNow(Carbon::createFromTimestamp($first_expire_time)->addMinute());

        $refresh_response = $this
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'RefreshAccessToken',
            ]);

        $refresh_response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'host' => [
                        'id' => $this->mainHost->id,
                        'name' => $this->mainHost->name,
                        'owner' => [
                            'id' => $this->mainUser->id,
                            'username' => $this->mainUser->username,
                        ],
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                ],
            ]);

        $new_token = $refresh_response->json('data.access_token');
        $later_expire_time = $jwt->parse($new_token)->getClaim('exp');
        $this->assertGreaterThan($first_expire_time, $later_expire_time);

        $this->mainHost->token->refresh();
        $this->assertEquals($later_expire_time, $this->mainHost->token->expires_at->getTimestamp());
    }

    /** @test */
    public function refreshingExpiredHostFails()
    {
        $this->mainHost->revoke();

        $this
            ->withExceptionHandling()
            ->withTokenFromHost($this->mainHost)
            ->postJson('/host', [
                'command' => 'RefreshAccessToken',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
