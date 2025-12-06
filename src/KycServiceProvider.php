<?php

namespace MetaDraw\Kyc;

use Illuminate\Support\ServiceProvider;
use MetaDraw\Kyc\Contracts\KycClient;
use MetaDraw\Kyc\Services\AliyunKycClient;
use MetaDraw\Kyc\Http\Middleware\KycVerifyThrottle;

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
            AliyunKycClient::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kyc.php' => $this->app->configPath('kyc.php'),
            ], 'kyc-config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => $this->app->databasePath('migrations'),
            ], 'kyc-migrations');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        
        $this->registerMiddleware();
    }
    
    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make('router');
        $router->aliasMiddleware('kyc.verify.throttle', KycVerifyThrottle::class);
    }
}