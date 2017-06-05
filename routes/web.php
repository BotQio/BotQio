<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('dashboard');
    }

    return view('welcome');
});

Auth::routes();

Route::get('dashboard', 'HomeController@index')
    ->name('dashboard');

Route::resource('bot', 'BotController');
Route::resource('cluster', 'ClusterController');
Route::resource('file', 'FileController');

Route::get('job/create/file/{file}', 'JobController@createFromFile')
    ->name('job.create.file');

Route::post('job/file', function ($request) {
    dd($request);
})->name('job.file.store');