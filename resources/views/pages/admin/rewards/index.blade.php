@extends('layouts.app')
@section('title', 'Katalog reward')
@section('heading', 'Katalog reward')
@section('eyebrow', 'Admin')
@section('subtitle', 'Hadiah ini dikelola admin dan tidak diambil dari produk yang dijual warga.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.tariff') }}">Kembali ke tarif</a>
    <a href="{{ route('admin.rewards.create') }}" class="btn primary">Tambah reward</a>
@endsection
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari nama atau kode reward" :filters="[['name' => 'status', 'label' => 'Semua status', 'options' => ['active' => 'Aktif', 'inactive' => 'Nonaktif']]]" />
    </div>
    <div class="grid three gap-top">
        @forelse ($rewards as $reward)
            <div class="card item-card">
                <div class="item-art product-photo">
                    @if ($reward->image_path)
                        <img src="{{ \App\Support\Uploads::url($reward->image_path) }}" alt="Foto {{ $reward->name }}">
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="">
                    @endif
                </div>
                <div>
                    <div class="row between"><h3>{{ $reward->name }}</h3><x-badge :status="$reward->is_active ? 'active' : 'inactive'" /></div>
                    <p>{{ $reward->description }}</p>
                </div>
                <div class="row between">
                    <span class="point-pill">{{ number_format($reward->points_required, 0, ',', '.') }} poin</span>
                    <span class="small muted">Stok {{ $reward->stock }}</span>
                </div>
                <a href="{{ route('admin.rewards.edit', $reward) }}" class="btn outline block">Ubah</a>
            </div>
        @empty
            <p class="small muted">Belum ada reward.</p>
        @endforelse
    </div>
    {{ $rewards->links() }}
@endsection
