@props(['script' => false])
@if (! $script)
    <style>
        #sirkula-splash{position:fixed;inset:0;z-index:120;display:grid;place-items:center;background:#f4f7ee;font-family:Inter,sans-serif}
        #sirkula-splash.is-done{opacity:0;visibility:hidden;pointer-events:none;transition:opacity .35s ease}
        .splash-card{width:min(460px,calc(100% - 32px));text-align:center;color:#26331a}
        .splash-card img{width:min(148px,42vw);height:auto;max-height:160px;object-fit:contain;background:transparent}
        .splash-card strong{display:block;margin-top:16px;font-size:28px;letter-spacing:.08em}
        .splash-card span{display:block;margin-top:4px;color:#5d6b52;font-size:13px}
        .splash-track{height:6px;margin:18px auto 0;overflow:hidden;border-radius:99px;background:#e2ead8}
        .splash-track i{display:block;width:40%;height:100%;border-radius:inherit;background:linear-gradient(90deg,#6f8f3a,#d9a500);animation:splash-slide 1.1s ease-in-out infinite}
        @keyframes splash-slide{0%{transform:translateX(-120%)}100%{transform:translateX(280%)}}
    </style>
    <div id="sirkula-splash" role="status" aria-live="polite">
        <div class="splash-card">
            <img src="{{ asset('images/logo.png') }}" alt="">
            <strong>SIRKULA</strong>
            <span>RW 13 · menyiapkan halaman</span>
            <div class="splash-track"><i></i></div>
        </div>
    </div>
@else
    <script>
        (() => {
            const splash = document.getElementById('sirkula-splash');
            if (!splash) return;
            const started = Date.now();
            const show = () => splash.classList.remove('is-done');
            const hide = () => {
                const wait = Math.max(0, 480 - (Date.now() - started));
                setTimeout(() => splash.classList.add('is-done'), wait);
            };
            if (document.readyState === 'complete') hide();
            else window.addEventListener('load', hide);
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) splash.classList.add('is-done');
            });
            window.addEventListener('pagehide', () => splash.classList.add('is-done'));
            document.addEventListener('click', (event) => {
                const link = event.target.closest('a');
                if (!link || link.target || link.hasAttribute('download') || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                const url = new URL(link.href, window.location.href);
                if (url.origin !== window.location.origin || url.href === window.location.href) return;
                show();
            });
            document.addEventListener('submit', (event) => {
                if (event.defaultPrevented) return;
                show();
            });
        })();
    </script>
@endif
