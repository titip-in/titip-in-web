<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PrelovedListingController;
use App\Http\Controllers\PrelovedRequestController;

Route::prefix('v1')->group(function () {
    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::get('/preloved/listings', [PrelovedListingController::class, 'index']);
    Route::get('/preloved/listings/{id}', [PrelovedListingController::class, 'show']);
    Route::get('/preloved/requests', [PrelovedRequestController::class, 'index']);
    Route::get('/preloved/requests/{id}', [PrelovedRequestController::class, 'show']);

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
    });
});