<?php

use App\Http\Controllers\CakeRequestController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommuneSearchController;
use App\Http\Middleware\EnsureShopIsOpen;
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

// Request forms (U5, U6): closed until the required settings are filled (KTD10),
// submissions rate limited per IP (KTD11). The thanks route is declared first so
// that "merci" is never read as a type slug.
Route::middleware(EnsureShopIsOpen::class)->controller(CakeRequestController::class)->group(function () {
    Route::get('/demande/merci', 'thanks')->name('request.thanks');
    Route::get('/demande-libre', 'createFree')->name('request.free.create');
    Route::post('/demande-libre', 'storeFree')
        ->middleware('throttle:5,60')
        ->name('request.free.store');
    Route::get('/demande/{cakeType:slug}', 'createForType')->name('request.type.create');
    Route::post('/demande/{cakeType:slug}', 'storeForType')
        ->middleware('throttle:5,60')
        ->name('request.type.store');
});

// Atelier (U7): private request photos, served only to a logged-in atelier account (KTD7).
// Class named as a string so this block needs no import at the top of the file.
Route::get('/atelier-fichiers/photos/{photo}', 'App\Http\Controllers\RequestPhotoController')
    ->name('atelier.request-photo');
