<?php

namespace Tests\Helpers;

use App;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

trait PassportHelper
{
    private $userClientSetUp = false;

    protected function setUpPersonalClient()
    {
        if ($this->userClientSetUp) {
            return;
        }

        $client = Passport::client()->forceFill([
            'user_id' => null,
            'name' => 'TestPersonalClient',
            'secret' => Str::random(40),
            'provider' => null,
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);

        $client->save();

        $accessClient = Passport::personalAccessClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();

        Config::set('passport.personal_access_client.id', $client->id);
        Config::set('passport.personal_access_client.secret', $client->secret);

        $this->userClientSetUp = true;
    }

    /**
     * @param $user App\Models\User
     * @param array $scopes
     * @return $this
     */
    public function withTokenFromUser($user, $scopes = '*')
    {
        $this->setUpPersonalClient();

        $token = $user->createToken('Test Token', Arr::wrap($scopes));

        $this->withAccessToken($token->accessToken);

        return $this;
    }

    /**
     * @param $host App\Models\Host
     * @return $this
     */
    public function withTokenFromHost($host)
    {
        $this->setUpPersonalClient();

        $tokenResult = $host->createToken("Test Token", ['host']);

        $host->withAccessToken($tokenResult->token);
        $this->withAccessToken($tokenResult->accessToken);

        return $this;
    }

    /**
     * @param $accessToken
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->withHeader('Authorization', 'Bearer '.$accessToken);

        return $this;
    }
}
