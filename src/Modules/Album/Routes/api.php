<?php

use Illuminate\Support\Facades\Route;
use Modules\Album\Http\Controllers\Api\Admin\AlbumController as AdminAlbumController;
use Modules\Album\Http\Controllers\Api\Admin\AlbumFileController as AdminAlbumFileController;
use Modules\Album\Http\Controllers\Api\Admin\AlbumImageController as AdminAlbumImageController;
use Modules\Album\Http\Controllers\Api\Admin\DashboardController;
use Modules\Album\Http\Controllers\Api\Admin\PurchaseController as AdminPurchaseController;
use Modules\Album\Http\Controllers\Api\AlbumController;
use Modules\Album\Http\Controllers\Api\DownloadController;
use Modules\Album\Http\Controllers\Api\PurchaseController;
use Modules\Album\Http\Controllers\Api\TapWebhookController;

/*
|--------------------------------------------------------------------------
| Album Module API Routes
|--------------------------------------------------------------------------
|
| These routes are automatically prefixed with /api by the module's
| service provider and use the standard "api" middleware group.
|
*/

// ---------------------------------------------------------------------
// Public: browse & search published albums
// ---------------------------------------------------------------------
Route::prefix('albums')->name('api.albums.')->group(function () {
    Route::get('/', [AlbumController::class, 'index'])->name('index');
    Route::get('/{album:slug}', [AlbumController::class, 'show'])->name('show');
});

// ---------------------------------------------------------------------
// Customer: purchases & protected downloads
// ---------------------------------------------------------------------
Route::middleware(['auth:web,api', 'throttle:20,1'])->group(function () {
    Route::post('albums/{album:slug}/purchase', [PurchaseController::class, 'store'])->name('api.albums.purchase');
    Route::get('purchases', [PurchaseController::class, 'index'])->name('api.purchases.index');
    Route::get('purchases/{purchase:uuid}', [PurchaseController::class, 'show'])->name('api.purchases.show');
});

Route::middleware(['auth:web,api', 'throttle:30,1'])
    ->get('albums/{album:slug}/files/{file}/download', [DownloadController::class, 'download'])
    ->name('api.albums.files.download');

// ---------------------------------------------------------------------
// Tap Payments: webhook (server-to-server, no user session)
// ---------------------------------------------------------------------
Route::post('payments/tap/webhook', [TapWebhookController::class, 'handle'])
    ->name('api.payments.tap.webhook');

// ---------------------------------------------------------------------
// Admin: album, media & sales management
// ---------------------------------------------------------------------
Route::prefix('admin')->name('api.admin.')->middleware(['auth:web,api', 'album.admin'])->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    Route::get('albums', [AdminAlbumController::class, 'index'])->name('albums.index');
    Route::post('albums', [AdminAlbumController::class, 'store'])->name('albums.store');
    Route::get('albums/{album:slug}', [AdminAlbumController::class, 'show'])->name('albums.show');
    Route::match(['put', 'post'], 'albums/{album:slug}', [AdminAlbumController::class, 'update'])->name('albums.update');
    Route::delete('albums/{album:slug}', [AdminAlbumController::class, 'destroy'])->name('albums.destroy');
    Route::post('albums/{album:slug}/publish-toggle', [AdminAlbumController::class, 'togglePublish'])->name('albums.publish-toggle');

    Route::post('albums/{album:slug}/images', [AdminAlbumImageController::class, 'store'])->name('albums.images.store');
    Route::delete('albums/{album:slug}/images/{image}', [AdminAlbumImageController::class, 'destroy'])->name('albums.images.destroy');
    Route::post('albums/{album:slug}/images/reorder', [AdminAlbumImageController::class, 'reorder'])->name('albums.images.reorder');
    Route::post('albums/{album:slug}/images/{image}/cover', [AdminAlbumImageController::class, 'setCover'])->name('albums.images.cover');

    Route::post('albums/{album:slug}/files', [AdminAlbumFileController::class, 'store'])->name('albums.files.store');
    Route::delete('albums/{album:slug}/files/{file}', [AdminAlbumFileController::class, 'destroy'])->name('albums.files.destroy');
    Route::post('albums/{album:slug}/files/reorder', [AdminAlbumFileController::class, 'reorder'])->name('albums.files.reorder');

    Route::get('purchases', [AdminPurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/{purchase:uuid}', [AdminPurchaseController::class, 'show'])->name('purchases.show');
});
