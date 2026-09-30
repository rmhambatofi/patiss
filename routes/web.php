<?php

use App\Http\Controllers\CommuneSearchController;
use App\Models\DeliveryTier;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// U4 — communes et paliers de livraison
Route::get('/communes', CommuneSearchController::class)->name('communes.search');
Route::get('/livraison', fn () => view('livraison', [
    'tiers' => DeliveryTier::orderBy('max_km')->get(),
]))->name('delivery.index');
