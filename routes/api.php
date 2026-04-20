<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PrelovedItemController;

Route::prefix('v1')->group(function () {
    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::get('/preloved', [PrelovedItemController::class, 'index']);
    Route::get('/preloved/{id}', [PrelovedItemController::class, 'show']);

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

        Route::post('/preloved', [PrelovedItemController::class, 'store']);
        Route::match(['put', 'patch'], '/preloved/{id}', [PrelovedItemController::class, 'update']);
        Route::delete('/preloved/{id}', [PrelovedItemController::class, 'destroy']);
    });
});