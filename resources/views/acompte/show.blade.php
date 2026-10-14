@php($euros = fn (int $cents) => \App\Support\Money::format($cents))
<x-layouts.public title="Acompte de votre commande">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <h1 class="heading-2">Réserver votre date</h1>
    <p class="mt-2 text-muted">Référence de votre demande : <strong class="font-mono tracking-widest text-ink">{{ $request->reference }}</strong></p>

    <dl class="mt-6 grid gap-x-6 gap-y-3 rounded-card bg-white p-5 shadow-card sm:grid-cols-[max-content_1fr] sm:p-8">
        <dt class="font-semibold">Gâteau</dt>
        <dd>{{ $request->typeLabel() }}, {{ $request->portions }} parts</dd>

        <dt class="font-semibold">Date</dt>
        <dd>{{ $request->event_date->locale('fr')->translatedFormat('l j F Y') }}</dd>

        <dt class="font-semibold">Créneau</dt>
        <dd>{{ $link->slot }}</dd>

        <dt class="font-semibold">Prix total{{ $request->fulfilment === \App\Enums\Fulfilment::Delivery ? ', livraison comprise' : '' }}</dt>
        <dd>{{ $euros($link->total_cents) }}</dd>

        <dt class="font-semibold">Acompte à payer maintenant</dt>
        <dd class="text-lg font-semibold">{{ $euros($link->deposit_cents) }}</dd>

        <dt class="font-semibold">Solde</dt>
        <dd>{{ $euros($link->balanceCents()) }}, à régler à la remise du gâteau</dd>

        <dt class="font-semibold">Allergènes</dt>
        <dd>
            @if ($link->allergens->isEmpty())
                Ce gâteau ne contient aucun des 14 allergènes réglementaires.
            @else
                {{ $link->allergens->map->label()->join(', ') }}
            @endif
        </dd>
    </dl>

    @if (filled($link->message))
        <div class="mt-6 rounded-card bg-lilac-3 p-5">{!! nl2br(e($link->message)) !!}</div>
    @endif

    <p class="mt-6">
        Ce lien est valable jusqu’au {{ $link->expires_at->timezone('Europe/Paris')->locale('fr')->translatedFormat('l j F Y \à H\hi') }}.
        Votre date n’est réservée qu’une fois l’acompte payé.
    </p>

    @if ($request->fulfilment === \App\Enums\Fulfilment::Delivery)
        <p class="mt-2 text-sm text-muted">L’adresse de livraison vous sera demandée sur la page de paiement.</p>
    @endif

    <form method="POST" action="{{ route('deposit.pay', $token) }}" class="mt-6 space-y-4">
        @csrf
        <label class="flex items-start gap-3">
            <input type="checkbox" name="cgv" value="1" required class="mt-1 size-4" @checked(old('cgv'))>
            <span>J’ai lu et j’accepte les <a href="{{ route('legal.terms') }}" target="_blank" class="link">conditions générales de vente</a>, dont les conditions d’annulation.</span>
        </label>
        @error('cgv')
            <p class="text-sm text-red-700">{{ $message }}</p>
        @enderror

        <x-ds.button type="submit" class="w-full sm:w-auto">
            Payer l’acompte de {{ $euros($link->deposit_cents) }}
        </x-ds.button>
        <p class="text-sm text-muted">Paiement sécurisé par carte bancaire (Apple Pay et Google Pay acceptés) sur la page Stripe.</p>
    </form>
</x-layouts.public>
