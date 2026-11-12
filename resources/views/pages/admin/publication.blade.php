@extends('layouts.app')
@section('title', 'Persetujuan publikasi')
@section('heading', 'Persetujuan publikasi')
@section('eyebrow', 'Draft diperiksa sebelum tampil')
@section('subtitle', 'Kegiatan dan produk baru hanya tampil ke publik setelah disetujui.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="grid two">
        <div class="card">
            <h3 class="card-title">Kegiatan</h3>
            @forelse ($events as $event)
                <div class="list-row"><div class="grow"><h4>{{ $event->title }}</h4><p>{{ $event->event_date?->locale('id')->translatedFormat('d M Y') }}</p></div><x-badge :status="$event->status" /></div>
            @empty
                <p class="small muted">Belum ada kegiatan.</p>
            @endforelse
            {{ $events->links() }}
        </div>
        <div class="card">
            <h3 class="card-title">Produk warga</h3>
            @forelse ($products as $product)
                <div class="list-row"><div class="grow"><h4>{{ $product->name }}</h4><p>{{ \App\Support\Labels::money($product->price) }}</p></div><x-badge :status="$product->status" /></div>
            @empty
                <p class="small muted">Belum ada produk.</p>
            @endforelse
            {{ $products->links() }}
        </div>
    </div>
@endsection
