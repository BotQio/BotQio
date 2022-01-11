<?php

namespace App\Policies;


use App\Models\Host;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

trait UsesTokens
{
    /**
     * @param User|Host $authed
     * @return bool
     */
    protected function usingToken($authed): bool
    {
        return !is_null($authed->token());
    }

    /**
     * @param User|Host $authed
     * @param string $scope
     * @return void
     * @throws AuthorizationException
     */
    protected function userNeedsScope($authed, string $scope)
    {
        if ($authed instanceof User) {
            if ($this->usingToken($authed) && !$authed->tokenCan($scope)) {
                throw new AuthorizationException("Token does not have required scope \"{$scope}\"");
            }
        }
    }
}