<?php

namespace App\Providers;

use App\TestHelpers\TestBroadcaster;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        if ($this->app->environment('testing')) {
            Broadcast::extend('test', function () {
                return new TestBroadcaster();
            });
        }

        require base_path('routes/channels.php');
    }
}