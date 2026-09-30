<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommuneSearchController;
use App\Models\DeliveryTier;
use Illuminate\Support\Facades\Route;

Route::get('/', [CollectionController::class, 'home'])->name('home');
Route::get('/gateaux', [CollectionController::class, 'index'])->name('collection.index');
Route::get('/gateaux/{cakeType:slug}', [CollectionController::class, 'show'])->name('collection.show');
Route::get('/sitemap.xml', [CollectionController::class, 'sitemap'])->name('sitemap');

Route::get('/communes', CommuneSearchController::class)->name('communes.search');
Route::get('/livraison', fn () => view('livraison', [
    'tiers' => DeliveryTier::orderBy('max_km')->get(),
]))->name('delivery.index');
