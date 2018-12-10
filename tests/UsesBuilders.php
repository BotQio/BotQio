<?php

namespace Tests;

use App\Enums\HostRequestStatusEnum;
use Faker\Generator as Faker;
use Tests\Helpers\Models\BotBuilder;
use Tests\Helpers\Models\ClusterBuilder;
use Tests\Helpers\Models\HostBuilder;
use Tests\Helpers\Models\HostRequestBuilder;
use Tests\Helpers\Models\JobBuilder;
use Tests\Helpers\Models\UserBuilder;

use App\Oauth\OauthHostClient;
use Laravel\Passport\ClientRepository;

/**
 * Trait UsesBuilders
 * @package Tests
 * @property \App\User mainUser
 * @property \App\Host mainHost
 */
trait UsesBuilders
{
    private $lazyMainUser;
    private $lazyMainHost;
    private $hostClientSetUp = false;

    public function __get($name)
    {
        switch ($name) {
            case 'mainUser':
                if(! isset($this->lazyMainUser)) {
                    $this->lazyMainUser = $this->user()->create();
                }
                return $this->lazyMainUser;
            case 'mainHost':
                if(! isset($this->lazyMainHost)) {
                    $this->lazyMainHost = $this->host()->create();
                }
                return $this->lazyMainHost;
        }
        throw new \Exception("Missing attribute $name");
    }

    private function setUpHostClient()
    {
        if($this->hostClientSetUp) {
            return;
        }

        $clients = app(ClientRepository::class);

        $client = $clients->create(
            null,
            'TestHostClient',
            'http://localhost'
        );

        $accessClient = new OauthHostClient();
        $accessClient->client_id = $client->id;
        $accessClient->save();

        $this->hostClientSetUp = true;
    }

    public function user()
    {
        $faker = app(Faker::class);

        return (new UserBuilder())
            ->username($faker->name)
            ->email($faker->email)
            ->password($faker->password);
    }

    /**
     * @return ClusterBuilder
     */
    public function cluster()
    {
        $faker = app(Faker::class);

        return (new ClusterBuilder())
            ->creator($this->mainUser)
            ->name($faker->name);
    }

    /**
     * @return BotBuilder
     */
    public function bot()
    {
        $faker = app(Faker::class);

        return (new BotBuilder())
            ->creator($this->mainUser)
            ->name($faker->name)
            ->type('3d_printer');
    }

    /**
     * @return JobBuilder
     */
    public function job()
    {
        $faker = app(Faker::class);

        return (new JobBuilder())
            ->creator($this->mainUser)
            ->name($faker->name);
    }

    /**
     * @return HostBuilder
     */
    public function host()
    {
        $this->setUpHostClient();

        $faker = app(Faker::class);

        return (new HostBuilder())
            ->creator($this->mainUser)
            ->name($faker->name);
    }

    /**
     * @return HostRequestBuilder
     */
    public function hostRequest()
    {
        $this->setUpHostClient();

        return (new HostRequestBuilder());
    }
}