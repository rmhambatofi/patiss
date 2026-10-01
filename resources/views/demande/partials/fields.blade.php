{{-- Fields shared by the type and free request forms: date, retrait/livraison, photos, allergies, contact, source, terms. --}}
@php
    $leadDate = today()->addDays($leadDays);
    $inputClass = 'field-input';
@endphp

<div>
    <label for="event_date" class="field-label">Date de l’événement</label>
    <input type="date" id="event_date" name="event_date" required
        value="{{ old('event_date') }}"
        min="{{ today()->toDateString() }}"
        max="{{ today()->addMonths(12)->toDateString() }}"
        data-lead-date="{{ $leadDate->toDateString() }}"
        class="{{ $inputClass }}">
    <p class="mt-1 text-sm text-muted">
        Délai minimum : commande au moins {{ $leadDays }} {{ $leadDays > 1 ? 'jours' : 'jour' }} à l’avance.
        Pour une date plus proche, vous pouvez quand même envoyer votre demande : l'artisan vous confirmera s'il peut la prendre.
    </p>
    <p data-lead-warning hidden class="alert-warning mt-2" role="status">
        Cette date est plus proche que le délai minimum de {{ $leadDays }} jours. L’artisan vous confirmera s’il peut prendre votre commande.
    </p>
    @include('demande.partials.error', ['field' => 'event_date'])
</div>

<fieldset data-fulfilment>
    <legend class="field-label">Retrait ou livraison</legend>
    <div class="mt-2 flex flex-col gap-2">
        <label class="flex items-center gap-2">
            <input type="radio" name="fulfilment" value="pickup" @checked(old('fulfilment', 'pickup') === 'pickup')>
            Retrait gratuit à Dammartin-en-Goële (adresse communiquée après confirmation)
        </label>
        <label class="flex items-center gap-2">
            <input type="radio" name="fulfilment" value="delivery" @checked(old('fulfilment') === 'delivery') @disabled(! $hasDeliveryTiers)>
            Livraison (tarif selon la zone)
        </label>
    </div>

    <div class="mt-3" data-commune-block>
        <label for="commune_name" class="field-label">Votre commune (pour la livraison)</label>
        <input type="text" id="commune_name" name="commune_name" autocomplete="off"
            value="{{ old('commune_name') }}"
            placeholder="Nom ou code postal"
            data-commune-search="{{ route('communes.search') }}"
            aria-describedby="commune-fee"
            class="{{ $inputClass }}">
        <input type="hidden" name="commune_insee" value="{{ old('commune_insee') }}" data-commune-insee>
        <ul data-commune-results hidden class="mt-1 max-h-60 overflow-auto rounded-[20px] border border-line bg-white text-sm shadow-card"></ul>
        <p id="commune-fee" data-commune-fee class="mt-1 text-sm" role="status"></p>
        @include('demande.partials.error', ['field' => 'commune_insee'])
        <p class="mt-1 text-sm text-muted">
            <a href="{{ url('/livraison') }}" class="link" target="_blank">Voir les zones et tarifs de livraison</a>.
            L’adresse exacte de livraison vous sera demandée au moment de l’acompte.
        </p>
    </div>
    @include('demande.partials.error', ['field' => 'fulfilment'])
</fieldset>

<fieldset data-photos data-max-files="5" data-max-bytes="{{ 10 * 1024 * 1024 }}">
    <legend class="field-label">Photos (facultatif)</legend>
    <p class="text-sm text-muted">5 photos au plus, 10 Mo chacune, JPEG, PNG, WebP ou HEIC.</p>
    <div class="mt-2">
        <label for="photo_a_reproduire" class="field-label">Photo à reproduire sur le gâteau</label>
        <input type="file" id="photo_a_reproduire" name="photo_a_reproduire" accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif" class="mt-1 block w-full text-sm">
        @include('demande.partials.error', ['field' => 'photo_a_reproduire'])
    </div>
    <div class="mt-3">
        <label for="inspirations" class="field-label">Photos d’inspiration (4 au plus)</label>
        <input type="file" id="inspirations" name="inspirations[]" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif" class="mt-1 block w-full text-sm">
        @include('demande.partials.error', ['field' => 'inspirations'])
        @foreach ($errors->getMessages() as $key => $messages)
            @if (str_starts_with($key, 'inspirations.'))
                <p class="field-error">{{ $messages[0] }}</p>
            @endif
        @endforeach
    </div>
    <p data-photos-error hidden class="mt-2 text-sm text-red-700" role="alert"></p>
    <p class="mt-1 text-xs text-muted">Vos photos ne sont visibles que par l’artisan et sont supprimées 3 mois après l’événement.</p>
</fieldset>

<div>
    <label for="allergies" class="field-label">Allergies ou intolérances des convives (facultatif)</label>
    <textarea id="allergies" name="allergies" rows="2" class="{{ $inputClass }}" data-allergies>{{ old('allergies') }}</textarea>
    @include('demande.partials.error', ['field' => 'allergies'])
    <label class="mt-2 flex items-start gap-2 text-sm">
        <input type="checkbox" name="allergy_consent" value="1" class="mt-1" @checked(old('allergy_consent'))>
        <span>J’accepte que ces informations de santé soient utilisées uniquement pour préparer ma commande, puis supprimées 3 mois après l’événement (obligatoire si vous indiquez des allergies).</span>
    </label>
    @include('demande.partials.error', ['field' => 'allergy_consent'])
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="field-label">Nom</label>
        <input type="text" id="name" name="name" required autocomplete="name" value="{{ old('name') }}" class="{{ $inputClass }}">
        @include('demande.partials.error', ['field' => 'name'])
    </div>
    <div>
        <label for="email" class="field-label">E-mail</label>
        <input type="email" id="email" name="email" required autocomplete="email" value="{{ old('email') }}" class="{{ $inputClass }}">
        @include('demande.partials.error', ['field' => 'email'])
    </div>
    <div>
        <label for="phone" class="field-label">Téléphone</label>
        <input type="tel" id="phone" name="phone" required autocomplete="tel" value="{{ old('phone') }}" class="{{ $inputClass }}">
        @include('demande.partials.error', ['field' => 'phone'])
    </div>
</div>

<div>
    <label for="source" class="field-label">Comment avez-vous connu l’artisan ?</label>
    <select id="source" name="source" required class="{{ $inputClass }}">
        <option value="">Choisissez une réponse</option>
        @foreach (\App\Enums\Source::cases() as $source)
            <option value="{{ $source->value }}" @selected(old('source') === $source->value)>{{ $source->label() }}</option>
        @endforeach
    </select>
    @include('demande.partials.error', ['field' => 'source'])
</div>

{{-- Honeypot (KTD11): hidden from people and screen readers, filled by bots. --}}
<div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
    <label for="site_web">Site web</label>
    <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off" value="">
</div>

<section class="panel text-sm text-ink-2" aria-labelledby="conditions-title">
    <h2 id="conditions-title" class="heading-4">Avant d’envoyer</h2>
    <p class="mt-1">L’artisan vous répond avec le prix exact{{ $hasDeliveryTiers ? ', livraison comprise' : '' }}. La commande est confirmée par le paiement en ligne d’un acompte.</p>
    @if ($depositRate)
        <p class="mt-1">Acompte de {{ $depositRate }} % du prix.</p>
    @endif
    @if ($cancellationTerms)
        <p class="mt-1 whitespace-pre-line">{{ $cancellationTerms }}</p>
    @endif
    <p class="mt-2">
        <a href="{{ url('/cgv') }}" class="link" target="_blank">Conditions générales de vente</a>
        · <a href="{{ url('/confidentialite') }}" class="link" target="_blank">Politique de confidentialité</a>
        · <a href="{{ url('/mentions-legales') }}" class="link" target="_blank">Mentions légales</a>
    </p>
</section>

@include('demande.partials.error', ['field' => 'photos'])

<x-ds.button type="submit" class="w-full sm:w-auto">
    Envoyer ma demande
</x-ds.button>
