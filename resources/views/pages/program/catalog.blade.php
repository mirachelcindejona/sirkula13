@extends('layouts.app')
@section('title', 'Katalog warga')
@section('heading', 'Katalog warga')
@section('eyebrow', 'Program')
@section('subtitle', 'Produk ini dijual warga. Reward admin dikelola di menu terpisah.')
@section('content')
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($products as $product)
            <article class="card p-5">
                @php($photo = match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' })
                <div class="item-art product-photo"><img src="{{ asset($photo) }}" alt="Foto {{ $product->name }}"></div>
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-lg font-extrabold">{{ $product->name }}</h2>
                    <x-badge :status="$product->status" />
                </div>
                <p class="mt-2 text-sm text-muted">{{ $product->seller?->full_name ?: 'Tanpa penjual' }}</p>
                <p class="mt-2 font-extrabold">{{ \App\Support\Labels::money($product->price) }}</p>
            </article>
        @endforeach
    </div>
    {{ $products->links() }}
@endsection
