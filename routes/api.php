<?php

use App\Http\Controllers\MediaProxyController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\WeddingController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\GuestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\WeddingController as AdminWeddingController;
use App\Http\Controllers\Admin\MemoryController as AdminMemoryController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('weddings/{slug}')->group(function () {
    Route::get('/', [WeddingController::class, 'show']);
    Route::get('/guest/{token}', [WeddingController::class, 'guestInvite']);
    Route::post('/guest/{token}/rsvp', [RsvpController::class, 'store']);
    Route::get('/rsvps', [RsvpController::class, 'index']);
    Route::get('/memories', [MemoryController::class, 'index']);
    Route::post('/memories', [MemoryController::class, 'store']);
});

// R2'deki medyayı backend üzerinden proxy'ler (bkz: .r2.dev domain ISP engeli)
Route::get('/media/{path}', [MediaProxyController::class, 'show'])->where('path', '.*');

// v2 için ayrılmış alan: /api/admin/... (Sanctum auth ile panel endpointleri)

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/me', [AdminAuthController::class, 'me']);

        Route::middleware('role:admin,couple')->group(function () {
            Route::get('/weddings/{wedding:id}/guests', [GuestController::class, 'index']);
            Route::post('/weddings/{wedding:id}/guests', [GuestController::class, 'store']);
            Route::put('/weddings/{wedding:id}/guests/{guest}', [GuestController::class, 'update']);
            Route::delete('/weddings/{wedding:id}/guests/{guest}', [GuestController::class, 'destroy']);

            Route::get('/weddings/{wedding:id}/dashboard', [DashboardController::class, 'index']);
            Route::get('/weddings/{wedding:id}/export', [ExportController::class, 'guests']);

            Route::get('/weddings/{wedding:id}/memories', [AdminMemoryController::class, 'index']);
            Route::post('/weddings/{wedding:id}/memories/{memory}/approve', [AdminMemoryController::class, 'approve']);
            Route::post('/weddings/{wedding:id}/memories/{memory}/reject', [AdminMemoryController::class, 'reject']);
            Route::delete('/weddings/{wedding:id}/memories/{memory}', [AdminMemoryController::class, 'destroy']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/weddings', [AdminWeddingController::class, 'index']);
            Route::get('/settings', [SettingsController::class, 'index']);
        });
    });


});