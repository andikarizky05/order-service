<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Configure HTTP client for service-to-service communication
        Http::macro('userService', function () {
            return Http::baseUrl(env('USER_SERVICE_URL'))
                ->timeout(env('USER_SERVICE_TIMEOUT', 5))
                ->retry(3, 100);
        });
        
        Http::macro('productService', function () {
            return Http::baseUrl(env('PRODUCT_SERVICE_URL'))
                ->timeout(env('PRODUCT_SERVICE_TIMEOUT', 5))
                ->retry(3, 100);
        });
    }
}
