<?php

use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1 (v1)
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'Laravel 11 API Service is online.',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('health');

    // RESTful API Resources (Category & Product CRUD with Route Model Binding)
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
});
