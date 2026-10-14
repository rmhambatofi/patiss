<?php

namespace App\Providers;

use App\Services\StripeGateway;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeGateway::class, fn () => new StripeGateway(config('services.stripe.secret')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // When the site runs in HTTPS (recette, prod), every generated URL is https, even if PHP sees
        // the request as http. Otherwise the browser blocks as mixed content the Livewire upload and
        // preview URLs of the atelier, and the Stripe return URLs. Local http setups are unaffected.
        URL::forceHttps(str_starts_with((string) config('app.url'), 'https://'));
    }
}
