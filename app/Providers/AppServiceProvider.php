<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('production')) {
            $assetUrl = config('app.asset_url');

            if (is_string($assetUrl) && str_starts_with($assetUrl, 'http://')) {
                config(['app.asset_url' => preg_replace('/^http:/', 'https:', $assetUrl)]);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
