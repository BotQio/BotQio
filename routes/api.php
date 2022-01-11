<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::get('bots', 'BotController@index')->name('api.bots.index');
Route::get('bots/{bot}', 'BotController@show')->name('api.bots.view')
    ->middleware('can:view,bot');


Route::get('clusters/{cluster}', 'ClusterController@show')->name('api.clusters.view')
    ->middleware('can:view,cluster');


Route::get('hosts/{host}', 'HostController@show')->name('api.hosts.view')
    ->middleware('can:view,host');


Route::get('jobs/{job}', 'JobController@show')->name('api.jobs.view')
    ->middleware('can:view,job');


Route::get('users/{user}', 'UserController@show')->name('api.users.view')
    ->middleware('can:view,user');
