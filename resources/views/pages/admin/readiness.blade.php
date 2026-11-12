@extends('layouts.app')
@section('title', 'Kesiapan operasional')
@section('heading', 'Kesiapan operasional')
@section('eyebrow', 'Sebelum layanan dipakai warga')
@section('subtitle', 'Tandai hal yang masih perlu keputusan pengurus sebelum operasi harian berjalan.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.tariff') }}">Kembali ke tarif</a>
@endsection
@section('content')
    <div class="stack">
        @foreach ($items as $item)
            <article class="card">
                <div class="row between">
                    <div>
                        <div class="tiny muted">{{ $item->category }}</div>
                        <h3 class="card-title">{{ $item->title }}</h3>
                        <p class="card-subtitle">{{ $item->description }}</p>
                    </div>
                    <x-badge :status="$item->is_active ? 'active' : 'inactive'" />
                </div>
            </article>
        @endforeach
    </div>
    {{ $items->links() }}
@endsection
