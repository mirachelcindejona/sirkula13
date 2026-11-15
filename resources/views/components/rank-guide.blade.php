@props(['current' => null])
<button type="button" class="rank-info" data-rank-open data-current="{{ $current }}" aria-label="Lihat rentang poin tiap rank">
    <x-icon name="help" />
</button>
@php
    $showRankDialog = empty($GLOBALS['sirkulaRankDialog']);
    $GLOBALS['sirkulaRankDialog'] = true;
    $tiers = \App\Support\EcoRank::tiers();
@endphp
@if ($showRankDialog)
    <dialog id="rank-dialog" class="rank-dialog">
        <div class="rank-dialog-head">
            <div>
                <p class="eyebrow">EcoPoints</p>
                <h3>Rentang rank warga</h3>
                <p>Rank naik mengikuti total poin yang pernah Anda kumpulkan. Rank tetap aman saat poin ditukar dengan hadiah.</p>
            </div>
            <button type="button" class="rank-close" data-rank-close aria-label="Tutup">
                <x-icon name="x" />
            </button>
        </div>
        <ol class="rank-ladder">
            @foreach ($tiers as $tier)
                @php
                    $floor = number_format($tier['min'], 0, ',', '.');
                    $range = $tier['next'] === null
                        ? $floor.' poin atau lebih'
                        : $floor.' – '.number_format($tier['next'] - 1, 0, ',', '.').' poin';
                    $slug = \Illuminate\Support\Str::slug($tier['name']);
                @endphp
                <li class="rank-tier rank-tier-{{ $slug }}" data-rank="{{ $tier['name'] }}">
                    <span class="rank-medal" aria-hidden="true">{{ $tier['emoji'] }}</span>
                    <span>
                        <strong>{{ $tier['name'] }}</strong>
                        <em>{{ $range }}</em>
                    </span>
                    <span class="rank-you">Rank Anda</span>
                </li>
            @endforeach
        </ol>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('rank-dialog');
            if (!dialog) return;
            const paint = (name) => {
                dialog.querySelectorAll('[data-rank]').forEach((row) => {
                    row.classList.toggle('is-current', name !== '' && row.dataset.rank === name);
                });
            };
            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-rank-open]');
                if (!button) return;
                paint(button.dataset.current || '');
                if (!dialog.open) dialog.showModal();
            });
            dialog.querySelector('[data-rank-close]')?.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        })();
    </script>
@endif
