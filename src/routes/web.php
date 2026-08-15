<?php

use Illuminate\Support\Facades\Route;

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

// The "/" route is now served by the Album module's public homepage
// (Modules/Album/Routes/web.php -> HomePageController). A simple JSON
// health check is still available at /up (Laravel's built-in health route)
// and /api/status below.
Route::get('/api-status', function () {
    return response()->json([
        'name' => config('app.name'),
        'status' => 'OK',
        'api_version' => 'v1.0',
    ]);
});

