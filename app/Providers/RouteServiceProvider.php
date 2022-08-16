<?php

namespace App\Providers;

use App;
use App\Http\Controllers\HostApiController;
use App\Http\Controllers\OctoPrintController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Route::model('bot', App\Models\Bot::class);
        Route::model('cluster', App\Models\Cluster::class);
        Route::model('file', App\Models\File::class);
        Route::model('host', App\Models\Host::class);
        Route::model('host_request', App\Models\HostRequest::class);
        Route::model('job', App\Models\Job::class);
        Route::model('user', App\Models\User::class);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapHostRoutes();

        $this->mapOctoPrintRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace('App\Http\Controllers\Api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "host" routes for the application.
     *
     * @return void
     */
    protected function mapHostRoutes()
    {
        Route::post('host', [HostApiController::class, 'command']);
    }

    /**
     * Define the "OctoPrint" routes. This allows slicers that understand how to upload
     * a file to OctoPrint to upload a file to BotQio.
     *
     * @return void
     */
    protected function mapOctoPrintRoutes()
    {
        Route::macro('octoprint', function($base) {
            $base = rtrim($base, '/');

            Route::prefix('octoprint')
                ->middleware('octoprint')
                ->group(function() use ($base) {
                    Route::get("$base/api/version", [OctoPrintController::class, 'version']);

                    Route::post("$base/api/files/local", [OctoPrintController::class, 'upload'])
                        ->middleware(['auth:octoprint_api']);
                });
        });

        Route::octoprint('bot/{bot}');
    }
}
