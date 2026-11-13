@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Katalog produk')
@section('heading', 'Katalog produk')
@section('eyebrow', 'Produk hasil pemanfaatan')
@section('subtitle', 'Pilih produk, lalu lanjutkan percakapan di WhatsApp penjual. Pesan sudah terisi nama produk dan kodenya.')
@section('actions')
    <a class="btn outline" href="{{ route('public.donation') }}"><x-icon name="heart" /> Dukung program</a>
@endsection
@section('content')
    @php
        $user = auth()->user();
        $approved = $user?->hasRole('warga') && $user->residentProfile?->status === 'active';
        $waiting = $user?->hasRole('warga') && $user->residentProfile && $user->residentProfile->status !== 'active';
    @endphp
    @guest
        <div class="public-subpage-head">
            <div class="eyebrow">SIRKULA-CERDAS · RW 13</div>
            <h1>Katalog produk</h1>
            <p>Pilih produk, lalu lanjutkan percakapan di WhatsApp penjual. Pesan sudah terisi nama produk dan kodenya.</p>
        </div>
    @endguest

    <div class="callout">
        <x-icon name="box" />
        <div><strong>Pesanan dilanjutkan lewat WhatsApp</strong>Chat sudah berisi nama produk dan kodenya. Penjual menandai pesanan setelah barang diterima.</div>
    </div>

    <div class="grid three gap-top">
        @forelse ($products as $product)
            @php($reserved = $product->orders->whereNotIn('status', ['cancelled', 'declined', 'completed'])->sum('quantity'))
            @php($available = max(0, (int) $product->stock - (int) $reserved))
            @php($photo = $product->image_path ? \App\Support\Uploads::url($product->image_path) : asset(match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' }))
            <article class="card item-card">
                <div class="item-art product-photo"><img src="{{ $photo }}" alt="Foto {{ $product->name }}"></div>
                <div>
                    <div class="row between">
                        <span class="badge info">{{ $available > 0 ? 'Tersedia' : 'Stok habis' }}</span>
                        <span class="small muted">Stok tersedia {{ number_format($available, 0, ',', '.') }}</span>
                    </div>
                    <h3 class="gap-top">{{ $product->name }}</h3>
                    <p class="small muted">{{ $product->code }} · {{ $product->seller?->full_name ?: ($product->contact_name ?: 'Pengurus RW') }}</p>
                    <p>{{ $product->description }}</p>
                </div>
                <div class="grow">
                    <div class="price">{{ \App\Support\Labels::money($product->price) }}</div>
                    <div class="small muted">Per produk</div>
                </div>
                @guest
                    <a class="btn primary block" href="{{ route('login') }}">Masuk untuk memesan</a>
                @else
                    @php($ownProduct = (int) $product->seller_profile_id === (int) $user?->residentProfile?->id)
                    @if ($ownProduct)
                        <button class="btn primary block" type="button" disabled>Ini produk Anda</button>
                    @elseif ($approved && $available > 0)
                        <form method="POST" action="{{ route('public.catalog.order', $product) }}" data-confirm="Anda akan diarahkan ke WhatsApp penjual dengan pesan yang sudah terisi. Lanjutkan?">
                            @csrf
                            <button class="btn primary block" type="submit">Pesan lewat WhatsApp</button>
                        </form>
                    @elseif ($waiting)
                        <button class="btn primary block" type="button" disabled>Bisa dipesan setelah akun disetujui</button>
                    @elseif ($available < 1)
                        <button class="btn primary block" type="button" disabled>Stok habis</button>
                    @else
                        <button class="btn primary block" type="button" disabled>Pesan lewat WhatsApp</button>
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
