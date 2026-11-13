@php
    $toasts = array_values(array_filter([
        session('success') ? ['type' => 'success', 'title' => 'Berhasil', 'icon' => 'check', 'message' => session('success')] : null,
        session('warning') ? ['type' => 'warning', 'title' => 'Perlu perhatian', 'icon' => 'alert', 'message' => session('warning')] : null,
        session('error') ? ['type' => 'error', 'title' => 'Tidak dapat diproses', 'icon' => 'alert', 'message' => session('error')] : null,
    ]));
@endphp
@if ($toasts)
    <div class="toast-stack" aria-live="polite">
        @foreach ($toasts as $toast)
            <article class="toast toast-{{ $toast['type'] }}" data-toast data-life="5200" role="status">
                <span class="toast-icon"><x-icon :name="$toast['icon']" /></span>
                <div class="toast-copy">
                    <strong>{{ $toast['title'] }}</strong>
                    <p>{{ $toast['message'] }}</p>
                </div>
                <button class="toast-close" type="button" data-toast-close aria-label="Tutup pesan"><x-icon name="x" /></button>
                <span class="toast-bar"></span>
            </article>
        @endforeach
    </div>
    <script>
        (() => {
            const dismiss = (toast) => {
                if (toast.dataset.closing === '1') return;
                toast.dataset.closing = '1';
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 260);
            };
            document.querySelectorAll('[data-toast]').forEach((toast) => {
                const life = Number(toast.dataset.life || 5200);
                const bar = toast.querySelector('.toast-bar');
                let remaining = life;
                let started = performance.now();
                let timer = window.setTimeout(() => dismiss(toast), remaining);
                const pause = () => {
                    remaining -= performance.now() - started;
                    window.clearTimeout(timer);
                    if (bar) bar.style.animationPlayState = 'paused';
                };
                const resume = () => {
                    if (toast.dataset.closing === '1') return;
                    started = performance.now();
                    if (bar) bar.style.animationPlayState = 'running';
                    timer = window.setTimeout(() => dismiss(toast), Math.max(remaining, 0));
                };
                toast.addEventListener('mouseenter', pause);
                toast.addEventListener('mouseleave', resume);
                toast.addEventListener('focusin', pause);
                toast.addEventListener('focusout', (event) => {
                    if (!toast.contains(event.relatedTarget)) resume();
                });
                toast.querySelector('[data-toast-close]')?.addEventListener('click', () => {
                    window.clearTimeout(timer);
                    dismiss(toast);
                });
            });
        })();
    </script>
@endif
