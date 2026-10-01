// Request forms (U5): commune search with zone fee, lead-time warning, photo size/count check.
// The server validates everything again; this only gives early feedback on a phone.

const formatEuros = (cents) => {
    const decimals = cents % 100 === 0 ? 0 : 2;
    return (cents / 100).toLocaleString('fr-FR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + ' €';
};

function setupLeadWarning(form) {
    const date = form.querySelector('[data-lead-date]');
    const warning = form.querySelector('[data-lead-warning]');
    if (!date || !warning) return;

    const update = () => {
        // ISO dates (YYYY-MM-DD) compare correctly as strings.
        warning.hidden = !(date.value && date.value < date.dataset.leadDate);
    };
    date.addEventListener('input', update);
    date.addEventListener('change', update);
    update();
}

function setupCommune(form) {
    const search = form.querySelector('[data-commune-search]');
    if (!search) return;

    const insee = form.querySelector('[data-commune-insee]');
    const results = form.querySelector('[data-commune-results]');
    const fee = form.querySelector('[data-commune-fee]');
    const delivery = form.querySelector('input[name="fulfilment"][value="delivery"]');
    const pickup = form.querySelector('input[name="fulfilment"][value="pickup"]');
    const deliveryInitiallyDisabled = delivery?.disabled ?? true;
    let timer = null;
    let controller = null;

    const showFee = (commune) => {
        if (!commune) {
            fee.textContent = '';
            if (delivery && !deliveryInitiallyDisabled) delivery.disabled = false;
            return;
        }
        if (commune.pickup_only) {
            fee.textContent = `${commune.name} est hors de la zone de livraison : retrait uniquement.`;
            fee.className = 'alert-warning mt-2';
            if (delivery) {
                delivery.disabled = true;
                if (delivery.checked && pickup) pickup.checked = true;
            }
        } else {
            fee.textContent = `Livraison à ${commune.name} : ${formatEuros(commune.delivery_fee_cents)}.`;
            fee.className = 'mt-1 text-sm text-ink-2';
            if (delivery && !deliveryInitiallyDisabled) delivery.disabled = false;
        }
    };

    const choose = (commune) => {
        search.value = commune.name;
        insee.value = commune.insee_code;
        results.hidden = true;
        results.innerHTML = '';
        showFee(commune);
    };

    const render = (communes) => {
        results.innerHTML = '';
        communes.forEach((commune) => {
            const item = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'block w-full px-3 py-2 text-left hover:bg-lilac-2';
            button.textContent = `${commune.name} (${(commune.postal_codes || []).join(', ')})`;
            button.addEventListener('click', () => choose(commune));
            item.appendChild(button);
            results.appendChild(item);
        });
        results.hidden = communes.length === 0;
    };

    const lookup = async (term, exactInsee = null) => {
        controller?.abort();
        controller = new AbortController();
        try {
            const url = `${search.dataset.communeSearch}?q=${encodeURIComponent(term)}`;
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
            const communes = await response.json();
            if (exactInsee) {
                showFee(communes.find((c) => c.insee_code === exactInsee) ?? null);
            } else {
                render(communes);
            }
        } catch (error) {
            if (error.name !== 'AbortError') results.hidden = true;
        }
    };

    search.addEventListener('input', () => {
        insee.value = '';
        showFee(null);
        clearTimeout(timer);
        const term = search.value.trim();
        if (term.length < 2) {
            results.hidden = true;
            return;
        }
        timer = setTimeout(() => lookup(term), 200);
    });

    // After a failed submission, show the fee of the commune already chosen.
    if (insee.value && search.value.trim().length >= 2) {
        lookup(search.value.trim(), insee.value);
    }
}

function setupPhotos(form) {
    const box = form.querySelector('[data-photos]');
    if (!box) return;

    const inputs = box.querySelectorAll('input[type="file"]');
    const error = box.querySelector('[data-photos-error]');
    const maxFiles = Number(box.dataset.maxFiles);
    const maxBytes = Number(box.dataset.maxBytes);

    const check = () => {
        const files = [...inputs].flatMap((input) => [...input.files]);
        const inspirations = box.querySelector('input[name="inspirations[]"]');
        let message = '';
        if (files.length > maxFiles) {
            message = `Vous pouvez joindre ${maxFiles} photos au plus.`;
        } else if (inspirations && inspirations.files.length > maxFiles - 1) {
            message = `Au plus ${maxFiles - 1} photos d’inspiration.`;
        } else {
            const tooBig = files.find((file) => file.size > maxBytes);
            if (tooBig) message = `« ${tooBig.name} » dépasse 10 Mo.`;
        }
        error.textContent = message;
        error.hidden = message === '';
        return message === '';
    };

    inputs.forEach((input) => input.addEventListener('change', check));
    form.addEventListener('submit', (event) => {
        if (!check()) {
            event.preventDefault();
            error.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

document.querySelectorAll('[data-demande-form]').forEach((form) => {
    setupLeadWarning(form);
    setupCommune(form);
    setupPhotos(form);
});
