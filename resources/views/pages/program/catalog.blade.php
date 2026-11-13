@extends('layouts.app')
@section('title', 'Katalog produk')
@section('heading', 'Kelola katalog produk')
@section('eyebrow', 'Langsung tayang atau draft')
@section('subtitle', 'Tambah dan ubah produk pengurus atau kelompok RW. Produk milik warga diubah dari akun warga itu sendiri.')
@section('actions')
    <a class="btn primary" href="{{ route('program.catalog.create') }}">Tambah produk</a>
@endsection
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari nama produk atau penjual" :filters="[['name' => 'status', 'label' => 'Semua status', 'options' => ['published' => 'Tayang', 'draft' => 'Draft']]]" />
    </div>
    <div class="product-manage-grid gap-top">
        @forelse ($products as $product)
            @php($photo = $product->image_path ? \App\Support\Uploads::url($product->image_path) : asset(match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' }))
            <article class="card product-manage-card">
                <img src="{{ $photo }}" alt="Foto {{ $product->name }}">
                <div class="row between gap-top"><h3 class="card-title">{{ $product->name }}</h3><x-badge :status="$product->status" /></div>
                <p class="small muted">{{ $product->owner_kind === 'warga' ? ($product->seller?->full_name ?: 'Warga') : ($product->contact_name ?: 'Pengurus RW') }}</p>
                <p><strong>{{ \App\Support\Labels::money($product->price) }}</strong> · stok {{ $product->stock }}</p>
                <p class="small">{{ $product->description }}</p>
                @if ($product->owner_kind === 'warga')
                    <span class="small muted">Dikelola warga pemiliknya.</span>
                @else
                    <a class="btn small-btn outline" href="{{ route('program.catalog.edit', $product) }}">Ubah</a>
                @endif
            </article>
        @empty
            <p class="small muted">Belum ada produk.</p>
        @endforelse
    </div>
    {{ $products->links() }}
@endsection
