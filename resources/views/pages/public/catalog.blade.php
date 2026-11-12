@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Katalog produk')
@section('heading', 'Katalog produk')
@section('eyebrow', 'Produk hasil pemanfaatan')
@section('subtitle', 'Lihat harga, stok, dan status ketersediaan. Pemesanan diarahkan ke WhatsApp; transaksi dicatat kembali dengan kode referensi.')
@section('actions')
    <a class="btn outline" href="{{ route('public.donation') }}"><x-icon name="heart" /> Dukung program</a>
@endsection
@section('content')
    @php
        $user = auth()->user();
        $citizen = $user?->hasRole('warga') && $user->residentProfile?->status === 'active';
    @endphp
    @guest
        <div class="public-subpage-head">
            <div class="eyebrow">SIRKULA-CERDAS · RW 13</div>
            <h1>Katalog produk</h1>
            <p>Lihat harga, stok, dan status ketersediaan. Pemesanan diarahkan ke WhatsApp; transaksi dicatat kembali dengan kode referensi.</p>
        </div>
    @endguest

    <div class="callout">
        <x-icon name="box" />
        <div><strong>Pesanan dilanjutkan melalui WhatsApp</strong>Kode pesanan belum berarti transaksi selesai. Poin diberikan setelah penjual dan pembeli mengonfirmasi serah terima.</div>
    </div>

    <div class="grid three gap-top">
        @forelse ($products as $product)
            @php($reserved = $product->orders->whereNotIn('status', ['cancelled', 'declined', 'completed'])->sum('quantity'))
            @php($available = max(0, (int) $product->stock - (int) $reserved))
            @php($photo = match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' })
            <article class="card item-card">
                <div class="item-art product-photo"><img src="{{ asset($photo) }}" alt="Foto {{ $product->name }}"></div>
                <div>
                    <div class="row between">
                        <span class="badge info">{{ $available > 0 ? 'Tersedia' : 'Konfirmasi stok' }}</span>
                        <span class="small muted">Stok tersedia {{ number_format($available, 0, ',', '.') }}</span>
                    </div>
                    <h3 class="gap-top">{{ $product->name }}</h3>
                    <p>{{ $product->description }}</p>
                </div>
                <div class="grow">
                    <div class="price">{{ \App\Support\Labels::money($product->price) }}</div>
                    <div class="small muted">Per produk</div>
                </div>
                @guest
                    <a class="btn primary block" href="{{ route('login') }}">Buat kode pesanan</a>
                @else
                    @if ($citizen && $available > 0)
                        <form method="POST" action="{{ route('public.catalog.order', $product) }}" data-confirm="Buat kode pesanan untuk {{ $product->name }}?">
                            @csrf
                            <button class="btn primary block" type="submit">Buat kode pesanan</button>
                        </form>
                    @else
                        <button class="btn primary block" type="button" disabled>Buat kode pesanan</button>
                    @endif
                @endguest
            </article>
        @empty
            <div class="card empty-state"><h3>Belum ada produk tayang</h3><p>Produk akan muncul setelah draft ditinjau dan disetujui.</p></div>
        @endforelse
    </div>

    <div class="callout warning gap-top">
        <x-icon name="alert" />
        <div><strong>Harga dan stok perlu dikonfirmasi</strong>Pastikan harga, jumlah, dan waktu serah terima disepakati langsung dengan penjual.</div>
    </div>
@endsection
