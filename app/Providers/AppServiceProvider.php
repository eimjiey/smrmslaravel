<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use App\Http\Middleware\AdminMiddleware;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(Router $router): void
    {
        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('admin', AdminMiddleware::class);
        }
    }
}
