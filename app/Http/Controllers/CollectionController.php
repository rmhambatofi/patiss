<?php

namespace App\Http\Controllers;

use App\Models\CakeType;
use App\Settings\ShopSettings;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function home(): View
    {
        return view('home', ['types' => $this->visibleTypes()]);
    }

    public function index(): View
    {
        return view('collection.index', ['types' => $this->visibleTypes()]);
    }

    public function show(CakeType $cakeType, ShopSettings $settings): View
    {
        abort_unless($cakeType->isVisible(), 404);

        return view('collection.show', [
            'type' => $cakeType->load('photos'),
            'depositRate' => $settings->deposit_rate_percent,
            'cancellationTerms' => $settings->cancellation_terms,
        ]);
    }

    public function sitemap(): Response
    {
        return response()
            ->view('sitemap', ['types' => CakeType::visible()->get()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function visibleTypes()
    {
        return CakeType::visible()->with('photos')->get();
    }
}
