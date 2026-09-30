<?php

namespace App\Providers;

use App\Services\StripeGateway;
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
        //
    }
}
