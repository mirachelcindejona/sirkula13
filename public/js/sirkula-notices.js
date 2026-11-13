(() => {
    const root = document.getElementById('notice-root');
    if (!root) return;

    const bell = document.getElementById('notice-bell');
    const panel = document.getElementById('notice-panel');
    const list = document.getElementById('notice-list');
    const browserButton = document.getElementById('notice-browser');
    const count = root.querySelector('.notice-count');
    const seenKey = 'sirkula-notified-ids';
    let items = [];

    const readSeen = () => {
        try {
            return JSON.parse(localStorage.getItem(seenKey) || '[]');
        } catch {
            return [];
        }
    };

    const remember = (id) => {
        const seen = readSeen().filter((value) => value !== id);
        seen.push(id);
        localStorage.setItem(seenKey, JSON.stringify(seen.slice(-40)));
    };

    const paintCount = (unread) => {
        if (!count) return;
        count.hidden = unread < 1;
        count.textContent = unread > 9 ? '9+' : String(unread);
    };

    const paintList = () => {
        list.replaceChildren();
        if (items.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'small muted';
            empty.textContent = 'Belum ada pemberitahuan.';
            list.append(empty);
            return;
        }
        items.forEach((item) => {
            const link = document.createElement('a');
            link.className = 'notice-item' + (item.read ? '' : ' is-unread');
            link.href = item.url;
            const title = document.createElement('strong');
            title.textContent = item.title;
            const body = document.createElement('span');
            body.textContent = item.body;
            const time = document.createElement('small');
            time.textContent = item.time || '';
            link.append(title, body, time);
            list.append(link);
        });
    };

    const desktop = (item) => {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        const seen = readSeen();
        if (seen.includes(item.id) || item.read) return;
        remember(item.id);
        const popup = new Notification(item.title, { body: item.body, lang: 'id' });
        popup.onclick = () => {
            window.focus();
            window.location.href = item.url;
        };
    };

    const load = async () => {
        let response;
        try {
            response = await fetch(root.dataset.poll, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
        } catch {
            return;
        }
        if (response.status === 401) {
            const data = await response.json().catch(() => ({}));
            if ('Notification' in window && Notification.permission === 'granted' && data.message) {
                new Notification('Akun SIRKULA', { body: data.message, lang: 'id' });
            }
            window.location.href = root.dataset.login || '/masuk';
            return;
        }
        if (!response.ok) return;
        const data = await response.json();
        items = Array.isArray(data.items) ? data.items : [];
        paintCount(Number(data.unread) || 0);
        paintList();
        items.forEach(desktop);
    };

    bell?.addEventListener('click', () => {
        const open = panel.hidden;
        panel.hidden = !open;
        bell.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) load();
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            panel.hidden = true;
            bell?.setAttribute('aria-expanded', 'false');
        }
    });

    browserButton?.addEventListener('click', async () => {
        if (!('Notification' in window)) {
            browserButton.textContent = 'Peramban ini tidak mendukung notifikasi';
            return;
        }
        const result = await Notification.requestPermission();
        browserButton.textContent = result === 'granted'
            ? 'Notifikasi browser sudah nyala'
            : 'Notifikasi browser belum diizinkan';
        if (result === 'granted') load();
    });

    if ('Notification' in window && Notification.permission === 'granted') {
        browserButton.textContent = 'Notifikasi browser sudah nyala';
    }

    load();
    window.setInterval(load, 25000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') load();
    });
})();
