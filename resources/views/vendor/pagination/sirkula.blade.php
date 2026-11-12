@if ($paginator->hasPages())
    <nav class="pager" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="btn outline" aria-disabled="true">Sebelumnya</span>
        @else
            <a class="btn outline" href="{{ $paginator->previousPageUrl() }}">Sebelumnya</a>
        @endif
        <span class="pager-status">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a class="btn outline" href="{{ $paginator->nextPageUrl() }}">Berikutnya</a>
        @else
            <span class="btn outline" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
