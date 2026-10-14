// Vitrine : filtres de la galerie, visionneuse et fermeture du menu mobile.
// Tout fonctionne sans JavaScript (toutes les pièces affichées, pas de zoom) ; ceci ajoute le confort.

function setupGalleryFilters(gallery) {
    const buttons = gallery.querySelectorAll('[data-filter]');
    const cards = gallery.querySelectorAll('[data-category]');
    if (!buttons.length) return;

    const apply = (category) => {
        buttons.forEach((button) => {
            button.setAttribute('aria-pressed', button.dataset.filter === category ? 'true' : 'false');
        });
        cards.forEach((card) => {
            card.hidden = category !== '' && card.dataset.category !== category;
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => apply(button.dataset.filter));
    });
}

function setupLightbox(dialog) {
    const image = dialog.querySelector('[data-lightbox-image]');
    const category = dialog.querySelector('[data-lightbox-category]');
    const name = dialog.querySelector('[data-lightbox-name]');
    const description = dialog.querySelector('[data-lightbox-description]');

    document.querySelectorAll('[data-lightbox]').forEach((card) => {
        card.addEventListener('click', () => {
            image.src = card.dataset.image;
            image.alt = card.dataset.name;
            category.textContent = card.dataset.categoryLabel;
            name.textContent = card.dataset.name;
            description.textContent = card.dataset.description;
            dialog.showModal();
        });
    });

    // Un clic sur le voile (hors de la figure) ferme la visionneuse ; Échap est géré par <dialog>.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-lightbox-close]')) {
            dialog.close();
        }
    });
}

function setupMobileMenu(menu) {
    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => menu.removeAttribute('open'));
    });
}

document.querySelectorAll('[data-gallery]').forEach(setupGalleryFilters);
document.querySelectorAll('dialog[data-lightbox-dialog]').forEach(setupLightbox);
document.querySelectorAll('details[data-mobile-menu]').forEach(setupMobileMenu);
