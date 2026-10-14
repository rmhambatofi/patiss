<?php

namespace Database\Seeders;

use App\Enums\CreationCategory;
use App\Models\Creation;
use Illuminate\Database\Seeder;

/**
 * Les douze pièces de la maquette vitrine. Les photos sont des illustrations Pexels
 * (créditées dans le pied de page) à remplacer par les vraies photos dans l'atelier.
 * Idempotent : chaque pièce est retrouvée par son nom.
 */
class CreationSeeder extends Seeder
{
    public function run(): void
    {
        $pieces = [
            [CreationCategory::GateauxDeFete, 'Layer cake fleuri', 'Génoise vanille, crème légère, fleurs fraîches. Pour un anniversaire au jardin.', self::pexels(1070850), true],
            [CreationCategory::Tartes, 'Tarte aux fruits rouges', 'Pâte sablée, crème d\'amande, fruits du marché et sucre glace.', self::pexels(12496684), true],
            [CreationCategory::Entremets, 'Mousse chocolat & cacao', 'Chocolat de couverture 70 %, cœur fondant, cacao brut.', self::pexels(27525464), true],
            [CreationCategory::GateauxDeFete, 'Gâteau d\'anniversaire', 'Décor à la poche, bougies et beaucoup de couleurs.', self::pexels(1857157), false],
            [CreationCategory::Tartes, 'Tarte fraise & menthe', 'Fraises gariguette, crème pâtissière vanille, menthe du jardin.', self::pexels(6574533), true],
            [CreationCategory::Patisseries, 'Gâteau pommes-cannelle', 'Pommes rôties, croûte dorée, le goût du dimanche.', self::pexels(37325525), false],
            [CreationCategory::PetitsDesserts, 'Tartelettes fraises', 'Format individuel, crème diplomate, fraises entières.', self::pexels(31688649), false],
            [CreationCategory::GateauxDeFete, 'Pièce signature', 'Création sur mesure pour une fête de famille.', self::pexels(1721934), false],
            [CreationCategory::Entremets, 'Fondant chocolat rustique', 'Croûte craquelée, cœur moelleux, sans fioriture.', self::pexels(34325485), false],
            [CreationCategory::PetitsDesserts, 'Tartelettes crème & fraises', 'Chantilly mascarpone, pâte sucrée fine.', self::pexels(8176231), false],
            [CreationCategory::Tartes, 'Tarte aux pommes rustique', 'Pommes finement tranchées, caramel léger.', self::pexels(32822617), false],
            [CreationCategory::Patisseries, 'Table de fête', 'Assortiment de tartes et pâtisseries pour un brunch.', self::pexels(8176298), false],
        ];

        foreach ($pieces as $order => [$category, $name, $description, $imageUrl, $featured]) {
            Creation::updateOrCreate(['name' => $name], [
                'category' => $category,
                'description' => $description,
                'image_url' => $imageUrl,
                'sort_order' => $order + 1,
                'is_featured' => $featured,
                'is_published' => true,
            ]);
        }
    }

    private static function pexels(int $id): string
    {
        return "https://images.pexels.com/photos/{$id}/pexels-photo-{$id}.jpeg?auto=compress&cs=tinysrgb&w=1000";
    }
}
