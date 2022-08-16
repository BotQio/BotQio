<?php

namespace App\Providers;

use App;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
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

        Auth::viaRequest('octoprint-token', function (Request $request) {
            $apiToken = $request->header('X-Api-Key');
            abort_if(is_null($apiToken), 401, "No API Key found");

            /** @var App\Models\OctoPrintAPIUser $user */
            $user = App\Models\OctoPrintAPIUser::where('api_token', $apiToken)->first();
            abort_if(is_null($user), 401, "OctoPrint Token not found");

            return $user;
        });
    }
}
