@extends('layouts.app')
@section('title', 'Katalog produk')
@section('heading', 'Kelola katalog produk')
@section('eyebrow', 'Langsung tayang atau draft')
@section('subtitle', 'Produk yang ditayangkan langsung muncul untuk warga. Draft hanya terlihat di halaman ini.')
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
                <p class="small muted">{{ $product->seller?->full_name ?: 'Kelompok RW' }}</p>
                <p><strong>{{ \App\Support\Labels::money($product->price) }}</strong> · stok {{ $product->stock }}</p>
                <p class="small">{{ $product->description }}</p>
            </article>
        @empty
            <p class="small muted">Belum ada produk.</p>
        @endforelse
    </div>
    {{ $products->links() }}
@endsection
