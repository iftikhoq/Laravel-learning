<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'Laravel 11 API Service is online.',
            'version' => '1.4.0',
            'phase' => 'Phase 5: Frontend Integration, Middleware & Sanctum Auth',
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('health');

    // Public Authentication Endpoints (Rate limited to 5 attempts per minute to prevent brute-force)
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:auth')
            ->name('register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth')
            ->name('login');

        // Protected Authentication Endpoints (Requires valid Sanctum Token & Active User)
        Route::middleware(['auth:sanctum', 'active'])->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout.all');
        });

        // Test Utility Endpoint: Toggle user active state (for demonstrating EnsureCustomerIsActive middleware)
        Route::post('/toggle-active/{user}', [AuthController::class, 'toggleActive'])
            ->name('toggle.active');
    });

    // Public Catalog (Categories & Products reading)
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);

    // Admin-Only Catalog Management (Requires Sanctum Token, Active User, and Admin Policy)
    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    });
});
