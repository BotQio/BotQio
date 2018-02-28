<?php


namespace Tests;

use App;

trait PassportHelper
{
    /**
     * @param $user App\User
     * @param array $scopes
     * @return $this
     */
    public function withTokenFromUser($user, $scopes = ['*'])
    {
        if (! is_array($scopes)) {
            $scopes = [$scopes];
        }

        $token = $user->createToken('Test Token', $scopes);

        $this->withAccessToken($token->accessToken);

        return $this;
    }

    /**
     * @param $host App\Host
     */
    public function withTokenFromHost($host)
    {
        $accessToken = $host->getAccessToken();

        $jwtToken = (string)$accessToken->convertToJWT(passport_private_key());

        $this->withAccessToken($jwtToken);

        return $this;
    }

    /**
     * @param $accessToken
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->withHeader('Authorization', 'Bearer ' . $accessToken);

        return $this;
    }
}

class HostTokenHolder
{
    /**
     * @var App\Host
     */
    public $host;
    public $accessToken;

    /**
     * HostTokenHolder constructor.
     * @param $host App\Host
     * @param $accessToken
     */
    public function __construct($host, $accessToken)
    {
        $this->host = $host;
        $this->accessToken = $accessToken;
    }
}
