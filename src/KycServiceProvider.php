<?php

namespace MetaDraw\Kyc;

use Illuminate\Support\ServiceProvider;
use MetaDraw\Kyc\Contracts\KycClient;
use MetaDraw\Kyc\Services\TencentKycClient;

class KycServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/kyc.php', 'kyc'
        );

        $this->app->bind(
            KycClient::class,
            TencentKycClient::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kyc.php' => config_path('kyc.php'),
            ], 'kyc-config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'kyc-migrations');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kyc');
    }
}