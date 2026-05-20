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
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminManagementController;
use \App\Http\Controllers\Api\BoostController;

Route::prefix('v1')->group(function () {

    Route::get('/download/android', [DownloadController::class, 'downloadAndroid']);

    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::post('/email/verify', [AuthController::class, 'verifyEmail']);

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

    Route::middleware(['auth:sanctum', 'check.banned'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::middleware('throttle:otp-request')->group(function () {
            Route::post('/email/resend', [AuthController::class, 'resendEmail']);
            Route::post('/me/whatsapp/request-otp', [UserController::class, 'requestWaOtp']);
        });

        Route::get('/me', [UserController::class, 'show']);

        Route::middleware('throttle:posting')->group(function () {
            Route::match(['put', 'patch'], '/me', [UserController::class, 'update']);
            Route::put('/me/password', [UserController::class, 'changePassword']);
            Route::post('/me/whatsapp/verify-otp', [UserController::class, 'verifyWaOtp']);
        });

        Route::middleware('profile.completed')->group(function () {

            Route::post('/upload', [UploadController::class, 'uploadImage'])->middleware('throttle:posting');

            Route::middleware(['throttle:posting', 'check.limit'])->group(function () {
                Route::post('/preloved/listings', [PrelovedListingController::class, 'store']);
                Route::post('/preloved/requests', [PrelovedRequestController::class, 'store']);
                Route::post('/jastip/listings', [JastipListingController::class, 'store']);
                Route::post('/jastip/requests', [JastipRequestController::class, 'store']);
            });

            Route::middleware('throttle:posting')->group(function () {
                Route::match(['put', 'patch'], '/preloved/listings/{id}', [PrelovedListingController::class, 'update']);
                Route::delete('/preloved/listings/{id}', [PrelovedListingController::class, 'destroy']);

                Route::match(['put', 'patch'], '/preloved/requests/{id}', [PrelovedRequestController::class, 'update']);
                Route::delete('/preloved/requests/{id}', [PrelovedRequestController::class, 'destroy']);

                Route::match(['put', 'patch'], '/jastip/listings/{id}', [JastipListingController::class, 'update']);
                Route::delete('/jastip/listings/{id}', [JastipListingController::class, 'destroy']);

                Route::match(['put', 'patch'], '/jastip/requests/{id}', [JastipRequestController::class, 'update']);
                Route::delete('/jastip/requests/{id}', [JastipRequestController::class, 'destroy']);

                Route::post('/preloved/listings/{id}/boost', [BoostController::class, 'boostPrelovedListing']);
                Route::post('/preloved/requests/{id}/boost', [BoostController::class, 'boostPrelovedRequest']);
                Route::post('/jastip/listings/{id}/boost', [BoostController::class, 'boostJastipListing']);
                Route::post('/jastip/requests/{id}/boost', [BoostController::class, 'boostJastipRequest']);
            });

            Route::get('/me/jastip/listings', [UserActivityController::class, 'myJastipListings']);
            Route::get('/me/jastip/requests', [UserActivityController::class, 'myJastipRequests']);
            Route::get('/me/preloved/listings', [UserActivityController::class, 'myPrelovedListings']);
            Route::get('/me/preloved/requests', [UserActivityController::class, 'myPrelovedRequests']);
        });
    });

    Route::prefix('admin')->group(function () {
        
        Route::middleware('throttle:auth')->group(function () {
            Route::post('/login', [AdminAuthController::class, 'login']);
        });

        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            Route::post('/logout', [AdminAuthController::class, 'logout']);
            
            Route::get('/users', [AdminManagementController::class, 'getUsers']);
            Route::patch('/users/{id}/tier', [AdminManagementController::class, 'updateUserTier']);
            Route::post('/users/{id}/ban', [AdminManagementController::class, 'toggleBanUser']);

            Route::get('/items/{type}', [AdminManagementController::class, 'getItems']);
            Route::get('/items/{type}/{id}', [AdminManagementController::class, 'getItemDetail']);
            Route::delete('/items/{type}/{id}', [AdminManagementController::class, 'forceDeleteItem']);
        });
    });

});