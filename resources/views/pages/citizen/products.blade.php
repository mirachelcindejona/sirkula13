@extends('layouts.app')
@section('title', 'Produk saya')
@section('heading', 'Produk saya')
@section('eyebrow', 'Jualan Anda')
@section('subtitle', 'Pembeli akan menghubungi Anda lewat WhatsApp. Kalau barang sudah sampai, tandai di bagian Pesanan masuk.')
@section('actions')
    @if ($approved)
        <a class="btn primary" href="{{ route('citizen.products.create') }}">Tambah produk</a>
    @else
        <button class="btn primary" type="button" disabled>Tambah produk</button>
    @endif
@endsection
@section('content')
    @if ($products->isEmpty())
        <div class="card empty-state">
            <div class="empty-icon"><x-icon name="box" /></div>
            <h3>Belum ada produk</h3>
            <p>Punya barang hasil olahan sendiri? Pasang di sini, nanti tetangga bisa langsung pesan.</p>
            @if ($approved)
                <a class="btn primary" href="{{ route('citizen.products.create') }}">Tambah produk pertama</a>
            @endif
        </div>
    @else
        <div class="my-product-grid">
            @foreach ($products as $product)
                @php($photo = $product->image_path ? \App\Support\Uploads::url($product->image_path) : asset(match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' }))
                <article class="my-product {{ $product->status === 'archived' ? 'is-archived' : '' }}">
                    <div class="my-product-photo">
                        <img src="{{ $photo }}" alt="Foto {{ $product->name }}">
                        <x-badge :status="$product->status" />
                    </div>
                    <div class="my-product-body">
                        <h3>{{ $product->name }}</h3>
                        <p class="my-product-price">{{ \App\Support\Labels::money($product->price) }}</p>
                        <p class="small muted">Stok {{ $product->stock }} · {{ $product->code }}</p>
                    </div>
                    @if ($approved)
                        <div class="my-product-actions">
                            <a class="btn small-btn outline" href="{{ route('citizen.products.edit', $product) }}">Ubah</a>
                            @if ($product->status === 'archived')
                                <form method="POST" action="{{ route('citizen.products.publish', $product) }}">
                                    @csrf
                                    <button class="btn small-btn secondary" type="submit">Tayangkan lagi</button>
                                </form>
                            @elseif ($product->status === 'published')
                                <form method="POST" action="{{ route('citizen.products.archive', $product) }}" data-confirm="Sembunyikan {{ $product->name }} dari katalog? Bisa ditayangkan lagi kapan saja.">
                                    @csrf
                                    <button class="btn small-btn outline" type="submit">Arsipkan</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('citizen.products.destroy', $product) }}" data-confirm="Hapus {{ $product->name }}? Produk yang sudah pernah dipesan tidak bisa dihapus, cukup diarsipkan.">
                                @csrf
                                @method('DELETE')
                                <button class="btn small-btn danger-ghost" type="submit" aria-label="Hapus {{ $product->name }}">Hapus</button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        {{ $products->links() }}
    @endif

    <div class="card gap-top">
        <h3 class="card-title">Pesanan masuk</h3>
        <p class="card-subtitle">Tekan "Sudah diterima" setelah barang benar-benar sampai di pembeli.</p>
        <div class="table-wrap gap-top">
            <table>
                <thead><tr><th>Kode</th><th>Produk</th><th>Pembeli</th><th>Total</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($sales as $order)
                        <tr>
                            <td><strong>{{ $order->reference_code }}</strong></td>
                            <td>{{ $order->product?->name }}<br><span class="small muted">{{ $order->product?->code }}</span></td>
                            <td>{{ $order->buyer?->full_name ?: '—' }}</td>
                            <td>{{ \App\Support\Labels::money($order->total_amount) }}</td>
                            <td><x-badge :status="$order->status" /></td>
                            <td>
                                @if (in_array($order->status, ['awaiting_seller', 'awaiting_buyer'], true))
                                    <div class="row">
                                        <form method="POST" action="{{ route('citizen.sales.complete', $order) }}" data-confirm="Barang untuk pesanan {{ $order->reference_code }} sudah sampai di pembeli?">
                                            @csrf
                                            <button class="btn small-btn primary" type="submit">Sudah diterima</button>
                                        </form>
                                        <form method="POST" action="{{ route('citizen.sales.cancel', $order) }}" data-confirm="Batalkan pesanan {{ $order->reference_code }}?">
                                            @csrf
                                            <button class="btn small-btn outline" type="submit">Batalkan</button>
                                        </form>
                                    </div>
                                @elseif ($order->status === 'held_for_review')
                                    <span class="small muted">{{ $order->held_reason ?: 'Sedang dicek pengurus.' }}</span>
                                @else
                                    <span class="small muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada pesanan masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sales->links() }}
    </div>
@endsection
