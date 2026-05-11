<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PrelovedListingController;
use App\Http\Controllers\PrelovedRequestController;
use App\Http\Controllers\JastipRequestController;
use App\Http\Controllers\JastipListingController;

Route::prefix('v1')->group(function () {
    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::get('/preloved/listings', [PrelovedListingController::class, 'index']);
    Route::get('/preloved/listings/{id}', [PrelovedListingController::class, 'show']);
    Route::get('/preloved/requests', [PrelovedRequestController::class, 'index']);
    Route::get('/preloved/requests/{id}', [PrelovedRequestController::class, 'show']);

    Route::get('/jastip/listings', [JastipListingController::class, 'index']);
    Route::get('/jastip/listings/{id}', [JastipListingController::class, 'show']);
    Route::get('/jastip/requests', [JastipRequestController::class, 'index']);
    Route::get('/jastip/requests/{id}', [JastipRequestController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::get('/me', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved',
                'data' => $request->user()
            ]);
        });

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::match(['put', 'patch'], '/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::post('/preloved/listings', [PrelovedListingController::class, 'store']);
        Route::match(['put', 'patch'], '/preloved/listings/{id}', [PrelovedListingController::class, 'update']);
        Route::delete('/preloved/listings/{id}', [PrelovedListingController::class, 'destroy']);

        Route::post('/preloved/requests', [PrelovedRequestController::class, 'store']);
        Route::match(['put', 'patch'], '/preloved/requests/{id}', [PrelovedRequestController::class, 'update']);
        Route::delete('/preloved/requests/{id}', [PrelovedRequestController::class, 'destroy']);

        Route::post('/jastip/listings', [JastipListingController::class, 'store']);
        Route::match(['put', 'patch'], '/jastip/listings/{id}', [JastipListingController::class, 'update']);
        Route::delete('/jastip/listings/{id}', [JastipListingController::class, 'destroy']);

        Route::post('/jastip/requests', [JastipRequestController::class, 'store']);
        Route::match(['put', 'patch'], '/jastip/requests/{id}', [JastipRequestController::class, 'update']);
        Route::delete('/jastip/requests/{id}', [JastipRequestController::class, 'destroy']);
    });
});