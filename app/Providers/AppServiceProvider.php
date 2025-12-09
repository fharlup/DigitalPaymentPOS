<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Midtrans\Config; 
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
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        // Paksa matikan SSL Verification secara Global
        Config::$curlOptions = [
            CURLOPT_SSL_VERIFYPEER => false,
        ];
    }
}
