<?php

namespace App\Http\Middleware;

use App\Settings\ShopSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the request forms closed until the legal and sales settings are filled in (KTD10).
 */
class EnsureShopIsOpen
{
    public function __construct(private ShopSettings $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->settings->isOpenForRequests()) {
            return response()->view('shop.closed', status: 503);
        }

        return $next($request);
    }
}
