<?php

namespace App\Http\Controllers;

use App\Enums\ContactOccasion;
use App\Enums\CreationCategory;
use App\Models\Creation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

/**
 * Vitrine v1 : une seule page, sans commande ni paiement.
 */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        $creations = Creation::visible()->get();

        return view('home', [
            'creations' => $creations,
            'heroShots' => $this->heroShots($creations),
            'categories' => $creations->map->category->unique()->sortBy(fn (CreationCategory $c) => array_search($c, CreationCategory::cases(), true))->values(),
            'occasions' => ContactOccasion::cases(),
        ]);
    }

    /**
     * Les quatre photos du hero : les pièces mises en avant, complétées par les premières de la galerie.
     *
     * @param  Collection<int, Creation>  $creations
     * @return Collection<int, Creation>
     */
    private function heroShots(Collection $creations): Collection
    {
        return $creations->where('is_featured', true)
            ->concat($creations->where('is_featured', false))
            ->take(4)
            ->values();
    }
}
