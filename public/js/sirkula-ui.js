(() => {
    const eye = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeOff = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 0 0 4.2 4.2"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6 0 10 7 10 7a18 18 0 0 1-3.2 4.2"/><path d="M6.1 6.1C3.8 7.8 2 12 2 12s4 7 10 7a10 10 0 0 0 4.1-.9"/></svg>';

    const markRequired = () => {
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach((field) => {
            if (field.type === 'hidden') return;
            const label = field.id ? document.querySelector(`label[for="${CSS.escape(field.id)}"]`) : null;
            if (!label || label.querySelector('.required')) return;
            const star = document.createElement('span');
            star.className = 'required';
            star.textContent = '*';
            label.append(' ', star);
        });
    };

    const passwordToggles = () => {
        document.querySelectorAll('input[type="password"]').forEach((input) => {
            if (input.closest('.password-field')) return;
            const wrap = document.createElement('div');
            wrap.className = 'password-field';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'password-toggle';
            button.setAttribute('aria-label', 'Tampilkan kata sandi');
            button.innerHTML = eye;
            button.addEventListener('click', () => {
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                button.setAttribute('aria-label', showing ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
                button.innerHTML = showing ? eye : eyeOff;
            });
            wrap.appendChild(button);
        });
    };

    const photoBoxes = () => {
        document.querySelectorAll('.upload-box input[type="file"]').forEach((input) => {
            input.addEventListener('change', () => {
                const box = input.closest('.upload-box');
                const preview = box.querySelector('img');
                const hint = box.querySelector('.upload-box-hint');
                const file = input.files?.[0];
                box.classList.remove('is-invalid');
                if (!file) return;
                const allowed = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowed.includes(file.type) || file.size > 2 * 1024 * 1024) {
                    input.value = '';
                    box.classList.remove('has-preview');
                    box.classList.add('is-invalid');
                    if (preview) preview.hidden = true;
                    if (hint) hint.textContent = 'Gunakan JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.';
                    return;
                }
                if (preview) {
                    preview.src = URL.createObjectURL(file);
                    preview.hidden = false;
                }
                box.classList.add('has-preview');
                if (hint) hint.textContent = file.name;
            });
            box.addEventListener('dragover', (event) => {
                event.preventDefault();
                box.classList.add('is-drag');
            });
            box.addEventListener('dragleave', () => box.classList.remove('is-drag'));
            box.addEventListener('drop', (event) => {
                event.preventDefault();
                box.classList.remove('is-drag');
                if (!event.dataTransfer?.files?.length) return;
                input.files = event.dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    };

    const closeCombos = (except) => {
        document.querySelectorAll('.combo.open').forEach((combo) => {
            if (combo !== except) combo.classList.remove('open');
        });
    };

    const enhanceSelect = (select) => {
        if (select.dataset.comboReady || select.multiple || select.closest('.combo')) return;
        select.dataset.comboReady = '1';
        const combo = document.createElement('div');
        combo.className = 'combo';
        select.parentNode.insertBefore(combo, select);
        combo.appendChild(select);
        select.classList.add('combo-native');

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'combo-button';
        const panel = document.createElement('div');
        panel.className = 'combo-panel';
        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'combo-search';
        search.placeholder = 'Cari pilihan';
        search.setAttribute('aria-label', 'Cari pilihan');
        const list = document.createElement('div');
        list.className = 'combo-list';
        panel.append(search, list);
        combo.append(button, panel);

        const labelOf = (option) => option?.textContent?.trim() || 'Pilih';
        const paint = () => {
            const selected = select.selectedOptions[0];
            button.textContent = labelOf(selected);
            button.disabled = select.disabled;
            button.classList.toggle('is-placeholder', !select.value);
        };
        const render = () => {
            const term = search.value.trim().toLowerCase();
            list.replaceChildren();
            let count = 0;
            [...select.options].forEach((option) => {
                if (term && !option.textContent.toLowerCase().includes(term)) return;
                count += 1;
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'combo-option';
                item.textContent = labelOf(option);
                if (option.disabled) item.disabled = true;
                if (option.selected) item.classList.add('is-selected');
                item.addEventListener('click', () => {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    paint();
                    combo.classList.remove('open');
                });
                list.appendChild(item);
            });
            if (count === 0) {
                const empty = document.createElement('p');
                empty.className = 'combo-empty';
                empty.textContent = 'Tidak ada pilihan yang cocok.';
                list.appendChild(empty);
            }
        };

        button.addEventListener('click', () => {
            const willOpen = !combo.classList.contains('open');
            closeCombos();
            combo.classList.toggle('open', willOpen);
            if (willOpen) {
                search.value = '';
                render();
                search.focus();
            }
        });
        search.addEventListener('input', render);
        select.addEventListener('change', () => {
            button.classList.remove('is-invalid');
            paint();
        });
        select.addEventListener('invalid', (event) => {
            event.preventDefault();
            button.classList.add('is-invalid');
            button.focus();
        });
        select.form?.addEventListener('reset', () => {
            setTimeout(() => {
                button.classList.remove('is-invalid');
                paint();
            }, 0);
        });
        paint();
    };

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.combo')) closeCombos();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeCombos();
    });

    const fieldMessage = (field) => {
        if (field.validity.valueMissing) return 'Bagian ini wajib diisi.';
        if (field.validity.typeMismatch && field.type === 'email') return 'Isi email yang benar, misalnya nama@gmail.com.';
        if (field.validity.tooShort) return `Minimal ${field.minLength} karakter.`;
        if (field.validity.patternMismatch) return 'Formatnya belum sesuai.';
        if (field.validity.rangeUnderflow) return 'Angkanya masih kurang.';
        if (field.validity.rangeOverflow) return 'Angkanya melebihi batas.';
        return field.validationMessage || 'Periksa isian ini.';
    };

    const fieldHost = (field) => field.closest('.password-field') || field.closest('.upload-box') || field.closest('.combo') || field;

    const showFieldError = (field, message) => {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        field.closest('.combo')?.querySelector('.combo-button')?.classList.add('is-invalid');
        field.closest('.upload-box')?.classList.add('is-invalid');
        const host = fieldHost(field);
        const previous = host.previousElementSibling;
        if (previous?.classList?.contains('field-error')) {
            previous.textContent = message;
            return;
        }
        const note = document.createElement('p');
        note.className = 'field-error';
        note.textContent = message;
        host.before(note);
    };

    const clearFieldError = (field) => {
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
        field.closest('.combo')?.querySelector('.combo-button')?.classList.remove('is-invalid');
        field.closest('.upload-box')?.classList.remove('is-invalid');
        const previous = fieldHost(field).previousElementSibling;
        if (previous?.classList.contains('field-error')) previous.remove();
    };

    const formErrors = () => {
        const node = document.getElementById('form-errors');
        if (!node) return;
        let messages = {};
        try {
            messages = JSON.parse(node.textContent || '{}');
        } catch {
            return;
        }
        Object.entries(messages).forEach(([key, list]) => {
            const message = Array.isArray(list) ? list[0] : list;
            if (!message) return;
            const bracket = key.replace(/\.(\d+)/g, '[$1]');
            const field = document.querySelector(`[name="${CSS.escape(key)}"], [name="${CSS.escape(key)}[]"], [name="${CSS.escape(bracket)}"]`);
            if (field) showFieldError(field, message);
        });
    };

    const sanitizeAngka = (value) => {
        const grouped = String(value).trim().match(/^\d{1,3}(\.\d{3})+$/);
        const source = grouped ? String(value).replace(/\./g, '') : String(value);
        let out = '';
        let comma = false;
        for (const ch of source) {
            if (ch >= '0' && ch <= '9') out += ch;
            else if ((ch === ',' || ch === '.') && !comma) {
                out += ',';
                comma = true;
            }
        }
        return out;
    };

    const prepareDecimal = (input) => {
        if (!(input instanceof HTMLInputElement) || input.dataset.angka === '1' || input.type !== 'number') return;
        input.dataset.angka = '1';
        input.type = 'text';
        input.inputMode = 'decimal';
        input.autocomplete = 'off';
        input.spellcheck = false;
        if (input.value) input.value = sanitizeAngka(input.value);
        input.addEventListener('beforeinput', (event) => {
            if (event.data == null) return;
            const clean = sanitizeAngka(event.data);
            if (clean === event.data) return;
            event.preventDefault();
            const start = input.selectionStart ?? input.value.length;
            const end = input.selectionEnd ?? start;
            const merged = sanitizeAngka(input.value.slice(0, start) + clean + input.value.slice(end));
            input.value = merged;
            const pos = Math.min(merged.length, start + clean.length);
            input.setSelectionRange(pos, pos);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
        input.addEventListener('input', () => {
            const clean = sanitizeAngka(input.value);
            if (clean === input.value) return;
            const pos = input.selectionStart ?? clean.length;
            input.value = clean;
            input.setSelectionRange(Math.min(clean.length, pos), Math.min(clean.length, pos));
        });
    };

    const decimalMessage = (field) => {
        const raw = field.value.trim();
        if (raw === '') return field.required ? 'Bagian ini wajib diisi.' : '';
        if (!/^\d+(,\d+)?$/.test(raw)) return 'Isi angka. Untuk desimal, pakai koma.';
        const value = Number(raw.replace(',', '.'));
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        const step = field.getAttribute('step');
        if (min !== null && min !== '' && value < Number(min)) return 'Angkanya masih kurang.';
        if (max !== null && max !== '' && value > Number(max)) return 'Angkanya melebihi batas.';
        const stepNum = step && step !== 'any' ? Number(step) : null;
        if (raw.includes(',') && (stepNum === null || stepNum >= 1) && !Number.isInteger(value)) return 'Isi angka bulat, tanpa koma.';
        if (stepNum) {
            const base = min !== null && min !== '' ? Number(min) : 0;
            const scale = 1000;
            const scaled = Math.round((value - base) * scale);
            const stepScaled = Math.round(stepNum * scale);
            if (stepScaled > 0 && scaled % stepScaled !== 0) return 'Angkanya belum sesuai kelipatan.';
        }
        return '';
    };

    const liveFormSelector = 'form.table-search, form[data-live-search]';
    let listingToken = 0;
    let listingControl = null;

    const labelCombo = (select) => {
        const button = select.closest('.combo')?.querySelector('.combo-button');
        if (!button) return;
        const selected = select.selectedOptions[0];
        button.textContent = selected?.textContent?.trim() || 'Pilih';
        button.classList.toggle('is-placeholder', !select.value);
    };

    const hasElementAfter = (form) => {
        let sibling = form.nextSibling;
        while (sibling) {
            if (sibling.nodeType === 1) return true;
            sibling = sibling.nextSibling;
        }
        return false;
    };

    const resultsOf = (form) => {
        const card = form.closest('.card') || form;
        const nested = card !== form && card.contains(form);
        if (nested && hasElementAfter(form)) return { mode: 'tail', card, form };
        const next = card.nextElementSibling;
        if (!next) return null;
        if (next.matches('[data-live-results]') || next.querySelector('.table-wrap, nav.pager')) return { mode: 'card', card: next, form };
        return null;
    };

    const syncLiveForm = (current, incoming) => {
        [...current.elements].forEach((field) => {
            if (!field.name || field.type === 'hidden' || field.type === 'submit' || field.type === 'button' || field === document.activeElement) return;
            const match = incoming.elements.namedItem(field.name);
            const source = match instanceof RadioNodeList ? null : match;
            if (!source || !('value' in source) || field.value === source.value) return;
            field.value = source.value;
            if (field.tagName === 'SELECT') labelCombo(field);
        });
    };

    const mountListing = (node) => {
        node.querySelectorAll('select').forEach(enhanceSelect);
        node.querySelectorAll('input[type="number"]').forEach(prepareDecimal);
    };

    const swapListing = (currentHost, incomingHost) => {
        syncLiveForm(currentHost.form, incomingHost.form);
        if (currentHost.mode === 'tail') {
            while (currentHost.form.nextSibling) currentHost.form.nextSibling.remove();
            const nodes = [];
            let sibling = incomingHost.form.nextSibling;
            while (sibling) {
                nodes.push(sibling);
                sibling = sibling.nextSibling;
            }
            nodes.forEach((node) => currentHost.form.parentNode.appendChild(document.importNode(node, true)));
            mountListing(currentHost.form.parentNode);
            return;
        }
        const copy = document.importNode(incomingHost.card, true);
        currentHost.card.replaceWith(copy);
        mountListing(copy);
    };

    const refreshListing = async (form, url) => {
        const host = resultsOf(form);
        if (!host) {
            window.location.assign(url);
            return;
        }
        const token = ++listingToken;
        listingControl?.abort();
        listingControl = new AbortController();
        const x = window.scrollX;
        const y = window.scrollY;
        try {
            const response = await fetch(url, {
                signal: listingControl.signal,
                credentials: 'same-origin',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (token !== listingToken) return;
            const finalUrl = new URL(response.url, window.location.href);
            if (!response.ok || finalUrl.pathname.endsWith('/masuk')) {
                window.location.assign(finalUrl.pathname.endsWith('/masuk') ? response.url : url);
                return;
            }
            const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (token !== listingToken) return;
            const forms = [...document.querySelectorAll(liveFormSelector)];
            const incomingForm = [...doc.querySelectorAll(liveFormSelector)][forms.indexOf(form)];
            const incomingHost = incomingForm ? resultsOf(incomingForm) : null;
            if (!incomingHost) throw new Error('missing');
            swapListing(host, incomingHost);
            history.replaceState(null, '', url);
            window.scrollTo(x, y);
        } catch (error) {
            if (error?.name === 'AbortError') return;
            window.location.assign(url);
        }
    };

    const listingUrl = (form) => {
        const url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
        const params = new URLSearchParams(new FormData(form));
        if (!form.classList.contains('pager-size')) params.delete('page');
        [...params.keys()].forEach((key) => {
            if (params.get(key) === '') params.delete(key);
        });
        url.search = params.toString();
        return url;
    };

    const liveFormFrom = (node) => {
        const region = node.closest('[data-live-results]');
        const regionCard = region?.previousElementSibling;
        if (regionCard?.matches?.(liveFormSelector)) return regionCard;
        const regionForm = regionCard?.querySelector?.(liveFormSelector);
        if (regionForm) return regionForm;
        const card = node.closest('.card');
        if (!card) return null;
        const inside = card.querySelector(liveFormSelector);
        if (inside) return inside;
        const previous = card.previousElementSibling;
        if (previous?.matches?.(liveFormSelector)) return previous;
        return previous?.querySelector?.(liveFormSelector) || null;
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const live = form.matches(liveFormSelector) || form.classList.contains('pager-size');
        if (!live || (form.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
        event.preventDefault();
        event.stopPropagation();
        const owner = form.matches(liveFormSelector) ? form : liveFormFrom(form);
        if (!owner) {
            window.location.assign(listingUrl(form));
            return;
        }
        refreshListing(owner, listingUrl(form.classList.contains('pager-size') ? form : owner));
    }, true);

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a');
        if (!link || link.target || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const inPager = link.closest('nav.pager');
        const inSearch = link.closest(liveFormSelector);
        if (!inPager && !inSearch) return;
        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;
        const form = inSearch ? link.closest(liveFormSelector) : liveFormFrom(link);
        if (!form) return;
        event.preventDefault();
        event.stopPropagation();
        refreshListing(form, url);
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        let blocked = false;
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            if (field.disabled || field.type === 'hidden') return;
            clearFieldError(field);
        });
        const password = form.querySelector('[name="password"]');
        const confirm = form.querySelector('[name="password_confirmation"]');
        if (password && confirm && password.value !== confirm.value) {
            showFieldError(confirm, 'Ulangan kata sandi belum sama.');
            blocked = true;
        }
        form.querySelectorAll('[data-angka]').forEach((field) => {
            const message = decimalMessage(field);
            if (!message) return;
            showFieldError(field, message);
            blocked = true;
        });
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            if (field.disabled || field.type === 'hidden' || field.dataset.angka === '1' || (field === confirm && password && password.value !== confirm.value)) return;
            if (!field.checkValidity()) {
                showFieldError(field, fieldMessage(field));
                blocked = true;
            }
        });
        if (blocked) {
            event.preventDefault();
            event.stopPropagation();
            form.querySelector('.is-invalid')?.focus();
            return;
        }
        form.querySelectorAll('[data-angka]').forEach((field) => {
            field.value = field.value.replace(',', '.');
        });
    }, true);

    document.addEventListener('input', (event) => {
        const field = event.target;
        if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
            if (field.validity.valid) clearFieldError(field);
        }
    });

    markRequired();
    passwordToggles();
    photoBoxes();
    document.querySelectorAll('select').forEach(enhanceSelect);
    document.querySelectorAll('input[type="number"]').forEach(prepareDecimal);
    formErrors();
})();
