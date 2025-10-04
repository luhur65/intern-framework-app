<?php

namespace App\Providers;

use App\Interfaces\PenjualanServiceInterface;
use App\Services\PenjualanService;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 *
 * This is the primary service provider for the application. It is used to
 * register services, events, and other bindings into the service container.
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is used to bind services into the container. Here, we are
     * binding the PenjualanServiceInterface to its concrete implementation,
     * PenjualanService, allowing for dependency injection throughout the application.
     *
     * @return void
     */
    public function register(): void
    {
        // Daftarkan binding antara interface dan implementasinya
        $this->app->bind(PenjualanServiceInterface::class, PenjualanService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other service providers have been registered,
     * meaning you have access to all other services that have been registered by
     * the framework. It's a good place for event listeners, routes, or any other
     * bootstrapping logic.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}