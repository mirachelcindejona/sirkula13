@if ($paginator->total() > 0 || $paginator->hasPages())
    @php
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $current = $paginator->currentPage();
        $last = max(1, $paginator->lastPage());
        $start = max(1, $current - 2);
        $end = min($last, $current + 2);
        $kept = request()->except(['per_page', 'page', 'menunggu', 'akun', 'kegiatan', 'produk', 'riwayat', 'pengeluaran']);
    @endphp
    <nav class="pager" aria-label="Halaman">
        <div class="pager-meta">
            <p>Menampilkan <strong>{{ $from }}–{{ $to }}</strong> dari {{ number_format($paginator->total(), 0, ',', '.') }} data</p>
            <form method="GET" action="{{ $paginator->path() }}" class="pager-size">
                @foreach ($kept as $name => $value)
                    @if (is_array($value))
                        @foreach ($value as $item)
                            <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="per-page-{{ $paginator->getPageName() }}">Baris</label>
                <select id="per-page-{{ $paginator->getPageName() }}" name="per_page" aria-label="Jumlah baris per halaman" onchange="this.form.requestSubmit()">
                    @foreach (\App\Support\Listing::SIZES as $size)
                        <option value="{{ $size }}" @selected(\App\Support\Listing::perPage() === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="pager-nav">
            @if ($paginator->onFirstPage())
                <span class="pager-btn" aria-disabled="true">
                    <x-icon name="arrow-left" />
                    <span class="pager-label">Sebelumnya</span>
                </span>
            @else
                <a class="pager-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                    <x-icon name="arrow-left" />
                    <span class="pager-label">Sebelumnya</span>
                </a>
            @endif
            <div class="pager-pages">
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page === $current)
                        <span class="pager-page is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pager-page" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor
            </div>
            @if ($paginator->hasMorePages())
                <a class="pager-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                    <span class="pager-label">Berikutnya</span>
                    <x-icon name="arrow-right" />
                </a>
            @else
                <span class="pager-btn" aria-disabled="true">
                    <span class="pager-label">Berikutnya</span>
                    <x-icon name="arrow-right" />
                </span>
            @endif
        </div>
    </nav>
@endif
