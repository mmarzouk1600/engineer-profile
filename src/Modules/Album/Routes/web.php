<?php

use Illuminate\Support\Facades\Route;
use Modules\Album\Http\Controllers\Api\TapWebhookController;
use Modules\Album\Http\Controllers\Web\Admin\AlbumPageController;
use Modules\Album\Http\Controllers\Web\Admin\DashboardPageController;
use Modules\Album\Http\Controllers\Web\Front\AlbumPageController as FrontAlbumPageController;
use Modules\Album\Http\Controllers\Web\Front\HomePageController;
use Modules\Album\Http\Controllers\Web\SessionAuthController;

/*
|--------------------------------------------------------------------------
| Album Module Web (Inertia) Routes
|--------------------------------------------------------------------------
|
| Page shells only — all data loading/mutation happens via the JSON API
| routes in Routes/api.php, called from the client with the JWT bearer
| token attached automatically (see resources/js/bootstrap.js).
|
*/

// Public / front site
Route::get('/', HomePageController::class)->name('home');
Route::get('/albums/{album:slug}', FrontAlbumPageController::class)->name('albums.show');
Route::get('/login', fn () => \Inertia\Inertia::render('Auth/Login'))->name('login');
Route::get('/register', fn () => \Inertia\Inertia::render('Auth/Register'))->name('register');

// Bridges a successful JWT login/logout into a real "web" session — needed
// because Inertia's own page navigation doesn't carry the JWT bearer header.
Route::post('/session/login', [SessionAuthController::class, 'login'])->name('session.login');
Route::post('/session/logout', [SessionAuthController::class, 'logout'])->name('session.logout');

// Fallback so Laravel's default auth redirect (Route::is('admin.*') ->
// route('admin.login')) never hits an undefined route when an unauthenticated
// visit hits a /dashboard/* page — just send them to the normal login page.
Route::get('/admin/login', fn () => redirect()->route('login'))->name('admin.login');

// Tap Payments — customer's browser lands here after checkout. Verifies the
// charge server-side (never trusts the redirect alone) then bounces the
// browser back to the album page with the outcome.
Route::get('/payment/tap/redirect', [TapWebhookController::class, 'redirect'])->name('payment.tap.redirect');

// Admin dashboard (route name prefix "admin." selects the admin Inertia
// bundle/root view — see App\Http\Middleware\HandleInertiaRequests).
Route::prefix('dashboard')->name('admin.')->middleware(['auth:web,api', 'album.admin'])->group(function () {
    Route::get('/', DashboardPageController::class)->name('dashboard');
    Route::get('/albums', [AlbumPageController::class, 'index'])->name('albums.index');
    Route::get('/albums/create', [AlbumPageController::class, 'create'])->name('albums.create');
    Route::get('/albums/{album:slug}/edit', [AlbumPageController::class, 'edit'])->name('albums.edit');
    Route::get('/purchases', [AlbumPageController::class, 'purchases'])->name('purchases.index');
});
