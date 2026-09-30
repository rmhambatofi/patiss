<x-layouts.public
    title="Retrait et livraison"
    description="Retrait gratuit à Dammartin-en-Goële, livraison en Île-de-France et dans l'Oise selon la distance."
>
    <div class="max-w-2xl space-y-8">
        <h1 class="text-3xl font-semibold">Retrait et livraison</h1>

        <section class="space-y-2">
            <h2 class="text-xl font-semibold">Retrait gratuit</h2>
            <p>Vous pouvez venir chercher votre commande à Dammartin-en-Goële, sans frais. L'adresse de retrait vous est communiquée à la confirmation de la commande.</p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-semibold">Livraison</h2>

            @if ($tiers->isEmpty())
                <p>Tarifs de livraison bientôt disponibles. Pour le moment, seul le retrait est possible.</p>
            @else
                <p>Le tarif dépend de la distance à vol d'oiseau entre Dammartin-en-Goële et votre commune :</p>
                <ul class="list-disc space-y-1 pl-6">
                    @foreach ($tiers as $tier)
                        <li>jusqu'à {{ $tier->maxKmLabel() }} km : {{ $tier->priceLabel() }}</li>
                    @endforeach
                </ul>
                <p>Au-delà de {{ $tiers->last()->maxKmLabel() }} km, ou en dehors de l'Île-de-France et de l'Oise, nous proposons uniquement le retrait.</p>
                <p class="text-sm text-stone-600">Le tarif de votre commune s'affiche dans le formulaire de demande, dès que vous la saisissez.</p>
            @endif
        </section>
    </div>
</x-layouts.public>
