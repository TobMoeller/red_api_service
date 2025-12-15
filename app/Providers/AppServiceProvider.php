<?php

namespace App\Providers;

use App\Services\RedProviderPortal\Clients\HttpRedProviderClient;
use App\Services\RedProviderPortal\Clients\MockRedProviderClient;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            RedProviderClient::class,
            function (): RedProviderClient {
                $config = Config::get('services.red_provider_portal', []);

                if (empty($config['base_url'])
                    || empty($config['client_id'])
                    || empty($config['client_secret'])
                    || empty($config['cert_path'])
                ) {
                    Log::debug('Invalid RED Provider Portal Config', ['config' => $config]);
                    throw Exception('Invalid RED Provider Portal Config');
                }

                if ($config['use_mock'] ?? false) {
                    return new MockRedProviderClient();
                }

                return new HttpRedProviderClient(
                    $config['base_url'],
                    $config['client_id'],
                    $config['client_secret'],
                    $config['cert_path'],
                );
            }
        );
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
