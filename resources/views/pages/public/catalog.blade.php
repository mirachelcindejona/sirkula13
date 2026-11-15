@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Katalog produk')
@section('heading', 'Katalog produk')
@section('eyebrow', 'Buatan warga RW 13')
@section('subtitle', 'Tekan Pesan, lalu ngobrol langsung dengan penjual di WhatsApp soal harga dan waktu ambil barang.')
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
            <p>Tekan Pesan, lalu ngobrol langsung dengan penjual di WhatsApp soal harga dan waktu ambil barang.</p>
        </div>
    @endguest

    <div class="grid three">
        @forelse ($products as $product)
            @php($reserved = $product->orders->whereNotIn('status', ['cancelled', 'declined', 'completed'])->sum('quantity'))
            @php($available = max(0, (int) $product->stock - (int) $reserved))
            @php($photo = $product->image_path ? \App\Support\Uploads::url($product->image_path) : asset(match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' }))
            <article class="card item-card" id="produk-{{ $product->id }}">
                <div class="item-art product-photo"><img src="{{ $photo }}" alt="Foto {{ $product->name }}"></div>
                <div>
                    <div class="row between">
                        <span class="badge info">{{ $available > 0 ? 'Tersedia' : 'Stok habis' }}</span>
                        <span class="small muted">Sisa {{ number_format($available, 0, ',', '.') }}</span>
                    </div>
                    <h3 class="gap-top">{{ $product->name }}</h3>
                    <p class="small muted">{{ $product->code }} · {{ $product->seller?->full_name ?: ($product->contact_name ?: 'Pengurus RW') }}</p>
                    <p>{{ $product->description }}</p>
                </div>
                <div class="grow">
                    <div class="price">{{ \App\Support\Labels::money($product->price) }}</div>
                </div>
                @guest
                    <a class="btn primary block" href="{{ route('login') }}">Masuk untuk memesan</a>
                @else
                    @php($ownProduct = (int) $product->seller_profile_id === (int) $user?->residentProfile?->id)
                    @if ($ownProduct)
                        <button class="btn primary block" type="button" disabled>Ini produk Anda</button>
                    @elseif ($approved && $available > 0)
                        <form method="POST" action="{{ route('public.catalog.order', $product) }}" data-confirm="Buka WhatsApp penjual untuk memesan {{ $product->name }}?">
                            @csrf
                            <button class="btn primary block" type="submit">Pesan</button>
                        </form>
                    @elseif ($waiting)
                        <button class="btn primary block" type="button" disabled>Tunggu akun disetujui</button>
                    @elseif ($available < 1)
                        <button class="btn primary block" type="button" disabled>Stok habis</button>
                    @else
                        <button class="btn primary block" type="button" disabled>Pesan lewat WhatsApp</button>
                    @endif
                @endguest
            </article>
        @empty
            <div class="card empty-state"><h3>Belum ada yang jualan</h3><p>Nanti produk buatan warga muncul di sini.</p></div>
        @endforelse
    </div>
@endsection
