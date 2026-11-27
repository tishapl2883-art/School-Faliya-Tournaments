<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Match times are entered and shown in Indian time.
        config(['app.timezone' => 'Asia/Kolkata']);
        date_default_timezone_set('Asia/Kolkata');

        // Public HTTPS tunnels (e.g. localhost.run) may omit X-Forwarded-Proto; force https for non-local hosts.
        if (! app()->runningInConsole()) {
            $host = request()->getHost();
            $isLocal = in_array($host, ['127.0.0.1', 'localhost'], true)
                || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false && filter_var($host, FILTER_VALIDATE_IP) !== false;
            if (! $isLocal) {
                URL::forceScheme('https');
            }
        }

        // Makes $site (event settings) available in every page.
        View::composer('*', function ($view) {
            static $site = null;
            // The fallback covers databases created before newer settings existed.
            $site ??= Setting::map() + ['tagline' => 'School Faliya Boys', 'payment_qr' => ''];
            $view->with('site', $site);
        });
    }
}
