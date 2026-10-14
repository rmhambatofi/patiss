<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Stripe signs its webhooks instead (KTD4).
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);

        // If a proxy in front of PHP handles HTTPS, trust only the scheme it forwards, so that
        // the signed Livewire upload URLs of the atelier match the https URL the browser calls.
        // X-Forwarded-For stays untrusted: the IP-based throttle of the request forms cannot be spoofed.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_PROTO);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
