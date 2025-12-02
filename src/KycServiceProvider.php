<?php

namespace MetaDraw\Kyc;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        
        // Register repositories
        $this->app->singleton(\MetaDraw\Kyc\Repositories\KycVerificationRepository::class);
        
        // Register services
        $this->app->singleton(\MetaDraw\Kyc\Services\KycService::class);
        $this->app->singleton(\MetaDraw\Kyc\Services\UploadService::class);
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kyc');
        
        if (config('kyc.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            
            Route::prefix(config('kyc.routes.prefix', 'api'))
                ->name(config('kyc.routes.name', 'kyc.'))
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
                });
        }
    }
}