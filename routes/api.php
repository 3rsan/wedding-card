<?php

use App\Http\Controllers\MemoryController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\WeddingController;
use Illuminate\Support\Facades\Route;

Route::prefix('weddings/{slug}')->group(function () {
    Route::get('/', [WeddingController::class, 'show']);
    Route::get('/guest/{token}', [WeddingController::class, 'guestInvite']);
    Route::post('/guest/{token}/rsvp', [RsvpController::class, 'store']);
    Route::get('/rsvps', [RsvpController::class, 'index']);
    Route::get('/memories', [MemoryController::class, 'index']);
    Route::post('/memories', [MemoryController::class, 'store']);
});

// v2 için ayrılmış alan: /api/admin/... (Sanctum auth ile panel endpointleri)
