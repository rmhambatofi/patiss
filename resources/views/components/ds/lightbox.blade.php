{{-- DS Lightbox : visionneuse <dialog> remplie par app.js depuis les cartes [data-lightbox]. Fond noir à 60 %, image rayon 24, légende blanche, bouton Fermer. --}}
<dialog data-lightbox-dialog class="m-0 h-full max-h-none w-full max-w-none animate-fadein cursor-zoom-out border-0 bg-transparent p-6 backdrop:bg-scrim">
    <figure class="mx-auto flex h-full max-w-[900px] cursor-default flex-col items-center justify-center gap-4">
        <img data-lightbox-image src="" alt="" class="max-h-[72vh] max-w-full rounded-md object-contain">
        <figcaption class="flex flex-col items-center gap-1 text-center text-white">
            <span data-lightbox-category class="text-label leading-label font-semibold text-faint"></span>
            <span data-lightbox-name class="text-h4 leading-card font-bold"></span>
            <span data-lightbox-description class="text-body-sm text-faint"></span>
        </figcaption>
    </figure>
    <button type="button" data-lightbox-close aria-label="Fermer" class="absolute top-5 right-5 grid size-11 cursor-pointer place-items-center rounded-full border-0 bg-white text-ink">
        <x-ds.icon name="close" :size="20" />
    </button>
</dialog>
