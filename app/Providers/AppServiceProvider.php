<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewApiDocs', function ($user = null) {
            return true; 
        });

        Scramble::routes(function (Route $route) {
            return str($route->uri)->startsWith('api/');
        });
    }
}
