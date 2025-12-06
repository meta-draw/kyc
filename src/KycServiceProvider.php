<?php

namespace MetaDraw\Kyc;

use Illuminate\Support\ServiceProvider;
use MetaDraw\Kyc\Contracts\KycClient;
use MetaDraw\Kyc\Services\AliyunKycClient;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

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
        
        $this->configureRateLimiting();
    }
    
    /**
     * Configure rate limiting for KYC routes.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('kyc-verify', function (Request $request) {
            $userId = $request->user()->id;
            
            return [
                Limit::perMinute(1)->by($userId)->response(function () {
                    return response()->json(['error' => '请求过于频繁，请1分钟后再试'], 429);
                }),
                Limit::perDay(10)->by($userId)->response(function () {
                    return response()->json(['error' => '今日验证次数已达上限（10次）'], 429);
                }),
            ];
        });
    }
}