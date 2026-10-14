@inject('shop', 'App\Settings\ShopSettings')
@php
    $artisan = $shop->artisan_first_name;
    $city = $shop->workshopCity();
    $whatsapp = $shop->whatsappUrl();
    $sent = session('contact_sent');

    $values = [
        ['icon' => 'handmade', 'title' => 'Tout est fait main.', 'text' => 'Dans mon atelier, pièce par pièce, sans série.'],
        ['icon' => 'quality', 'title' => 'Des produits de saison.', 'text' => 'Beurre AOP, fruits du marché, chocolat de couverture.'],
        ['icon' => 'whatsapp', 'title' => 'Des projets sur mesure.', 'text' => 'On en parle par WhatsApp ou e-mail, réponse sous 48 h.'],
    ];
    $steps = [
        ['num' => '01', 'title' => 'Choisir', 'text' => 'Le marché le matin : fruits mûrs, œufs frais, beurre AOP.', 'image' => 'https://images.pexels.com/photos/5446791/pexels-photo-5446791.jpeg?auto=compress&cs=tinysrgb&w=800'],
        ['num' => '02', 'title' => 'Préparer', 'text' => 'Peser, fouetter, laisser reposer. Le temps fait partie de la recette.', 'image' => 'https://images.pexels.com/photos/37823207/pexels-photo-37823207.jpeg?auto=compress&cs=tinysrgb&w=800'],
        ['num' => '03', 'title' => 'Monter', 'text' => 'Couches, textures, équilibre. Chaque pièce est assemblée à la main.', 'image' => 'https://images.pexels.com/photos/6574597/pexels-photo-6574597.jpeg?auto=compress&cs=tinysrgb&w=800'],
        ['num' => '04', 'title' => 'Finir', 'text' => 'Le dressage, les derniers détails, la photo avant de servir.', 'image' => 'https://images.pexels.com/photos/38009052/pexels-photo-38009052.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ];
    // Témoignages fictifs : exemples de contenu, signalés comme tels sous le titre.
    $reviews = [
        ['text' => 'Le layer cake pour les 30 ans de ma sœur était magnifique et encore meilleur en bouche. Tout le monde a demandé le nom de l’artisane.', 'name' => 'Camille R.', 'occasion' => 'Anniversaire'],
        ['text' => 'Un échange simple par WhatsApp, un croquis, et le jour J une pièce exactement comme on l’imaginait.', 'name' => 'Inès & Thomas', 'occasion' => 'Mariage'],
        ['text' => ($artisan ?: 'L’artisane').' a su traduire notre thème en douceurs. Nos collaborateurs en parlent encore.', 'name' => 'Julien M.', 'occasion' => 'Événement d’entreprise'],
    ];
    $artisanPhoto = 'https://images.pexels.com/photos/16140003/pexels-photo-16140003.jpeg?auto=compress&cs=tinysrgb&w=1200';
    $channels = array_values(array_filter([
        $whatsapp ? ['href' => $whatsapp, 'icon' => 'whatsapp', 'label' => 'WhatsApp', 'detail' => $shop->whatsappDisplay(), 'external' => true] : null,
        $shop->instagramUrl() ? ['href' => $shop->instagramUrl(), 'icon' => 'instagram', 'label' => 'Instagram', 'detail' => '@'.ltrim($shop->instagram_handle, '@'), 'external' => true] : null,
        $shop->facebook_url ? ['href' => $shop->facebook_url, 'icon' => 'facebook', 'label' => 'Facebook', 'detail' => $shop->business_name, 'external' => true] : null,
        $shop->contact_email ? ['href' => 'mailto:'.$shop->contact_email, 'icon' => 'mail', 'label' => 'E-mail', 'detail' => $shop->contact_email, 'external' => false] : null,
    ]));
@endphp
<x-layouts.public
    :title="'Pâtisserie artisanale'.($city ? ' à '.$city : '')"
    :description="'Entremets, tartes et gâteaux de fête faits main'.($city ? ' à '.$city : '').'. Chaque pièce est pensée, montée et décorée à la main par '.($artisan ?: 'l’artisane').'.'"
    bare
    contact-bar
    photo-credit="Photos d’illustration : Pexels">
    <x-slot:head>
        @include('partials.structured-data')
    </x-slot:head>

    {{-- Hero --}}
    <section id="top" class="px-6 pt-20 pb-12">
        <div class="mx-auto flex max-w-site flex-col gap-12">
            <div class="flex flex-col items-center gap-6 text-center">
                <h1 class="heading-1 max-w-[900px]">La pâtisserie comme un art du quotidien.</h1>
                <p class="lead max-w-[560px]">
                    @if ($artisan)Je m’appelle {{ $artisan }}. @endif
                    Dans mon atelier{{ $city ? ' à '.$city : '' }}, chaque entremets, chaque tarte, chaque pièce est pensé, monté et décoré à la main.
                </p>
                <div class="flex flex-wrap justify-center gap-2">
                    <x-ds.button href="#creations">Voir mes créations</x-ds.button>
                    @if ($whatsapp)
                        <x-ds.button :href="$whatsapp" variant="secondary" target="_blank" rel="noreferrer"><x-ds.icon name="whatsapp" :size="18" />Écrire sur WhatsApp</x-ds.button>
                    @else
                        <x-ds.button href="#contact" variant="secondary">Écrire un message</x-ds.button>
                    @endif
                </div>
            </div>
            @if ($heroShots->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @foreach ($heroShots as $shot)
                        <x-ds.photo :src="$shot->imageUrl()" :alt="$shot->name" :label="$shot->name" :lazy="false" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Valeurs --}}
    <section class="px-6 pt-12 pb-20" aria-label="Ce qui compte">
        <div class="mx-auto grid max-w-site grid-cols-[repeat(auto-fit,minmax(min(100%,260px),1fr))] gap-4">
            @foreach ($values as $value)
                <x-ds.value-card :icon="$value['icon']" :title="$value['title']" :text="$value['text']" />
            @endforeach
        </div>
    </section>

    {{-- Créations --}}
    <section id="creations" class="scroll-mt-24 px-6 pb-30" aria-labelledby="creations-title" data-gallery>
        <div class="mx-auto flex max-w-site flex-col gap-8">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="flex max-w-[640px] flex-col gap-3">
                    <h2 id="creations-title" class="heading-2">Une sélection de mes pièces.</h2>
                    <p class="text-body leading-body text-pretty text-muted">Chaque pièce est unique. Cliquez sur une photo pour la voir en grand, et écrivez-moi si l’une d’elles vous inspire.</p>
                </div>
                @if ($categories->count() > 1)
                    <div class="seg" role="group" aria-label="Filtrer par catégorie">
                        <button type="button" class="seg-btn" data-filter="" aria-pressed="true">Tout</button>
                        @foreach ($categories as $category)
                            <button type="button" class="seg-btn" data-filter="{{ $category->value }}" aria-pressed="false">{{ $category->label() }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
            @if ($creations->isEmpty())
                <p class="text-body text-muted">Les premières pièces arrivent bientôt.</p>
            @else
                <div class="grid grid-cols-[repeat(auto-fill,minmax(min(100%,270px),1fr))] gap-4">
                    @foreach ($creations as $creation)
                        <x-ds.work-card :creation="$creation" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- L'artisan --}}
    <section id="artisan" class="scroll-mt-24 px-6 pb-30" aria-labelledby="artisan-title">
        <div class="mx-auto grid max-w-site grid-cols-[repeat(auto-fit,minmax(min(100%,320px),1fr))] items-center gap-12">
            <x-ds.photo :src="$artisanPhoto" :alt="($artisan ?: 'L’artisane').' décore un gâteau dans son atelier'" :label="($artisan ? $artisan.' · ' : '').'Artisan pâtissière'" label-position="bottom-center" grayscale />
            <div class="flex max-w-[520px] flex-col gap-6">
                <h2 id="artisan-title" class="heading-2">Pâtissière de formation, artisane par passion.</h2>
                <p class="lead">Formée à Lyon puis passée par des maisons gastronomiques, j’ai ouvert mon atelier pour retrouver l’essentiel : le geste, le produit, le temps de bien faire.</p>
                <p class="text-body leading-body text-pretty text-muted">Je travaille le beurre AOP, les fruits de saison et le chocolat de couverture. Pas d’arômes artificiels, pas de colorants inutiles. Je préfère une pièce sincère à une pièce parfaite.</p>
                <div class="grid grid-cols-3 gap-2">
                    <x-ds.stat value="12" label="ans de métier" />
                    <x-ds.stat value="100 %" label="fait main" />
                    @if ($city)
                        <x-ds.stat :value="$city" label="et alentours" />
                    @else
                        <x-ds.stat value="48 h" label="pour vous répondre" />
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- L'atelier --}}
    <section id="atelier" class="scroll-mt-24 px-6 pb-30" aria-labelledby="atelier-title">
        <div class="mx-auto flex max-w-site flex-col gap-10 rounded-md bg-surface p-(--block-pad)">
            <div class="flex max-w-[560px] flex-col gap-3">
                <h2 id="atelier-title" class="heading-2">Du geste à l’assiette.</h2>
                <p class="text-body leading-body text-pretty text-muted">Quatre temps qui reviennent chaque jour dans l’atelier. C’est là que tout se joue.</p>
            </div>
            <ol class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,220px),1fr))] gap-4">
                @foreach ($steps as $step)
                    <li><x-ds.step-card :num="$step['num']" :title="$step['title']" :text="$step['text']" :image="$step['image']" /></li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Témoignages --}}
    <section class="px-6 pb-30" aria-labelledby="avis-title">
        <div class="mx-auto flex max-w-site flex-col gap-8">
            <div class="flex flex-col items-center gap-2 text-center">
                <h2 id="avis-title" class="heading-2">Ce qu’ils en disent.</h2>
                <span class="text-label leading-label text-muted">Témoignages fictifs — exemples de contenu.</span>
            </div>
            <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,280px),1fr))] gap-4">
                @foreach ($reviews as $review)
                    <x-ds.review-card :text="$review['text']" :name="$review['name']" :occasion="$review['occasion']" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section id="contact" class="scroll-mt-24 px-6 pb-20" aria-labelledby="contact-title">
        <div class="mx-auto grid max-w-site grid-cols-[repeat(auto-fit,minmax(min(100%,320px),1fr))] items-stretch gap-4">
            <div class="flex flex-col gap-8 rounded-md bg-surface p-(--block-pad)">
                <div class="flex flex-col gap-3">
                    <h2 id="contact-title" class="heading-2">Une envie, une occasion, une question ?</h2>
                    <p class="lead">Écrivez-moi, je réponds sous 48 h.@if ($whatsapp) Le plus simple reste WhatsApp.@endif</p>
                </div>
                @if ($channels)
                    <div class="flex flex-col gap-2">
                        @foreach ($channels as $channel)
                            <x-ds.contact-link :href="$channel['href']" :icon="$channel['icon']" :label="$channel['label']" :detail="$channel['detail']" :external="$channel['external']" />
                        @endforeach
                    </div>
                @endif
                @if ($shop->workshop_address || $shop->workshop_hours)
                    <div class="mt-auto flex flex-col gap-1 text-body-sm leading-body-sm text-muted">
                        <span class="font-semibold text-ink">Atelier, sur rendez-vous.</span>
                        @if ($shop->workshop_address)<span>{{ $shop->workshop_address }}</span>@endif
                        @if ($shop->workshop_hours)<span>{{ $shop->workshop_hours }}</span>@endif
                    </div>
                @endif
            </div>

            <div class="card p-(--block-pad)">
                @if ($sent)
                    <div class="flex min-h-[360px] flex-col items-start justify-center gap-4">
                        <span class="squircle size-12 bg-primary text-white"><x-ds.icon name="check" :size="22" /></span>
                        <h3 class="heading-3">Merci, {{ $sent }}.</h3>
                        <p class="text-body leading-body text-muted">Votre message est bien parti. Je vous réponds sous 48 h.</p>
                        <x-ds.button href="{{ route('home') }}#contact" variant="soft" size="sm" class="mt-2">Envoyer un autre message</x-ds.button>
                    </div>
                @else
                    <form method="post" action="{{ route('contact.store') }}" class="flex flex-col gap-4" novalidate>
                        @csrf
                        <h3 class="heading-3 mb-2">Écrivez-moi.</h3>
                        @if ($errors->any())
                            <p class="alert-error" role="alert">Vérifiez les champs signalés ci-dessous.</p>
                        @endif
                        <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,200px),1fr))] gap-3">
                            <x-ds.field label="Prénom" for="first_name" name="first_name">
                                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="Camille" required autocomplete="given-name" maxlength="80" class="field-input">
                            </x-ds.field>
                            <x-ds.field label="E-mail ou téléphone" for="contact" name="contact">
                                <input type="text" id="contact" name="contact" value="{{ old('contact') }}" placeholder="camille@mail.fr" required autocomplete="email" maxlength="255" class="field-input">
                            </x-ds.field>
                        </div>
                        <fieldset class="flex flex-col gap-2">
                            <legend class="field-label">L’occasion</legend>
                            <div class="seg self-start">
                                @foreach ($occasions as $occasion)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="occasion" value="{{ $occasion->value }}" class="peer sr-only" @checked(old('occasion', $occasions[0]->value) === $occasion->value)>
                                        <span class="seg-btn seg-btn-sm inline-flex items-center peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-segment peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-ink">{{ $occasion->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @include('demande.partials.error', ['field' => 'occasion'])
                        </fieldset>
                        <x-ds.field label="Votre message" for="message" name="message">
                            <textarea id="message" name="message" rows="5" required maxlength="3000" placeholder="Parlez-moi de votre projet : date, nombre de personnes, envies, couleurs…" class="field-input">{{ old('message') }}</textarea>
                        </x-ds.field>
                        <div class="hidden" aria-hidden="true">
                            <label for="website">Site web</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                            <span class="max-w-[300px] text-label leading-label text-muted">Vos coordonnées servent uniquement à vous répondre.</span>
                            <x-ds.button type="submit">Envoyer</x-ds.button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <x-ds.lightbox />
</x-layouts.public>
