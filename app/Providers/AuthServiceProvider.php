<?php

namespace App\Providers;

use App;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        App\Models\Bot::class => App\Policies\BotPolicy::class,
        App\Models\Job::class => App\Policies\JobPolicy::class,
        App\Models\User::class => App\Policies\UserPolicy::class,
    ];

    public function register()
    {
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::tokensCan([
            'host' => 'Be a host',
            'bots' => 'View info about bots',
            'files' => 'View info about files',
            'jobs' => 'View info about jobs',
            'users' => 'View info about users',
        ]);
    }
}
