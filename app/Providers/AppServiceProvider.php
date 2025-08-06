<?php

namespace App\Providers;

use App\Interfaces\PenjualanServiceInterface;
use App\Services\PenjualanService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Daftarkan binding antara interface dan implementasinya
        $this->app->bind(PenjualanServiceInterface::class, PenjualanService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
