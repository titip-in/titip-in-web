<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PrelovedListingController;
use App\Http\Controllers\Api\PrelovedRequestController;
use App\Http\Controllers\Api\JastipRequestController;
use App\Http\Controllers\Api\JastipListingController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserActivityController;

Route::prefix('v1')->group(function () {
    
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

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

    Route::get('/search', [SearchController::class, 'search'])->middleware('throttle:ai-search');

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::get('/me', [UserController::class, 'show']);
        
        Route::middleware('throttle:posting')->group(function () {
            Route::match(['put', 'patch'], '/me', [UserController::class, 'update']);
            Route::post('/upload', [UploadController::class, 'uploadImage']);

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

        Route::get('/me/jastip/listings', [UserActivityController::class, 'myJastipListings']);
        Route::get('/me/jastip/requests', [UserActivityController::class, 'myJastipRequests']);
        Route::get('/me/preloved/listings', [UserActivityController::class, 'myPrelovedListings']);
        Route::get('/me/preloved/requests', [UserActivityController::class, 'myPrelovedRequests']);
    });
});