<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router; // Required Import
use App\Http\Middleware\AdminMiddleware; // Required Import

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
    public function boot(Router $router): void
    {
        // Register your custom middleware with the alias 'admin'
        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('admin', AdminMiddleware::class);
        }
    }
}