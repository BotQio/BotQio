<?php

namespace App\Providers;

use App;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Horizon::auth(function () {
            /** @var User $user */
            $user = Auth::user();

            return $user->is_admin;
        });

        Paginator::defaultView('vendor.pagination.paginator');

        Relation::morphMap([
            'bots' => App\Models\Bot::class,
            'clusters' => App\Models\Cluster::class,
        ]);

        Storage::disk('public')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            return URL::temporarySignedRoute(
                'files.download',
                $expiration,
                array_merge($options, ['path' => $path])
            );
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
