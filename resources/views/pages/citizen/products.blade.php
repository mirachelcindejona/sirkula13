@extends('layouts.app')
@section('title', 'Produk saya')
@section('heading', 'Produk saya')
@section('eyebrow', 'Jualan warga')
@section('subtitle', 'Unggah produk Anda, lalu tandai pesanan setelah barang diterima pembeli.')
@section('actions')
    @if ($approved)
        <a class="btn primary" href="{{ route('citizen.products.create') }}">Tambah produk</a>
    @else
        <button class="btn primary" type="button" disabled>Tambah produk</button>
    @endif
@endsection
@section('content')
    <div class="callout">
        <x-icon name="box" />
        <div>
            <strong>Pesanan masuk lewat WhatsApp</strong>
            Pembeli diarahkan ke nomor Anda dengan nama produk dan kodenya. Setelah barang sampai, tandai pesanan di tabel bawah.
        </div>
    </div>

    <div class="product-manage-grid gap-top">
        @forelse ($products as $product)
            @php($photo = $product->image_path ? \App\Support\Uploads::url($product->image_path) : asset(match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' }))
            <article class="card product-manage-card">
                <img src="{{ $photo }}" alt="Foto {{ $product->name }}">
                <div class="row between gap-top"><h3 class="card-title">{{ $product->name }}</h3><x-badge :status="$product->status" /></div>
                <p class="small muted">{{ $product->code }} · WhatsApp {{ $product->contact_phone ?: 'belum diisi' }}</p>
                <p><strong>{{ \App\Support\Labels::money($product->price) }}</strong> · stok {{ $product->stock }}</p>
                @if ($approved)
                    <a class="btn small-btn outline" href="{{ route('citizen.products.edit', $product) }}">Ubah</a>
                @endif
            </article>
        @empty
            <p class="small muted">Belum ada produk. Tambahkan kalau Anda ingin menjual hasil pemanfaatan.</p>
        @endforelse
    </div>
    {{ $products->links() }}

    <div class="card gap-top">
        <h3 class="card-title">Pesanan masuk</h3>
        <p class="card-subtitle">Tandai hanya setelah pembeli benar-benar menerima barang.</p>
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
                                    <form method="POST" action="{{ route('citizen.sales.complete', $order) }}" data-confirm="Tandai pesanan {{ $order->reference_code }} sudah diterima pembeli?">
                                        @csrf
                                        <button class="btn small-btn primary" type="submit">Sudah diterima</button>
                                    </form>
                                    <form method="POST" action="{{ route('citizen.sales.cancel', $order) }}" data-confirm="Batalkan pesanan {{ $order->reference_code }}?">
                                        @csrf
                                        <button class="btn small-btn outline" type="submit">Batalkan</button>
                                    </form>
                                @elseif ($order->status === 'held_for_review')
                                    <span class="small muted">{{ $order->held_reason ?: 'Sedang diperiksa pengurus.' }}</span>
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
