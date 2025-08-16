<?php

use App\Http\Middleware\BlockMethodRoute;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // My Custom Middleware
        $middleware->append(BlockMethodRoute::class);

        // Untuk tes ApacheJMeter
        $middleware->validateCsrfTokens(except: [
            '/penjualan', // Tambahkan path rute Anda di sini
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
