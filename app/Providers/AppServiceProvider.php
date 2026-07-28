<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $request = request();

            if ($request) {
                $baseUrl = rtrim($request->getBaseUrl(), '/');
                $baseUrl = preg_replace('#/index\\.php$#', '', $baseUrl);

                URL::forceRootUrl($request->getSchemeAndHttpHost() . $baseUrl);
                URL::forceScheme($request->getScheme());
            }
        } catch (\Throwable $exception) {
            // Keep URL generation functional even if the request context is unavailable.
        }
    }
}
