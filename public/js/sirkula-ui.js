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
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            if (field.disabled || field.type === 'hidden' || (field === confirm && password && password.value !== confirm.value)) return;
            if (!field.checkValidity()) {
                showFieldError(field, fieldMessage(field));
                blocked = true;
            }
        });
        if (blocked) {
            event.preventDefault();
            event.stopPropagation();
            form.querySelector('.is-invalid')?.focus();
        }
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
    formErrors();
})();
