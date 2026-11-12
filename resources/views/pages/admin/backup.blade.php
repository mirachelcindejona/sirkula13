@extends('layouts.app')
@section('title', 'Backup & pemulihan')
@section('heading', 'Backup & pemulihan')
@section('eyebrow', 'Data yang tersimpan')
@section('subtitle', 'Ringkasan jumlah catatan. Cadangan berkas database dilakukan oleh pengelola server, bukan dari tombol di halaman ini.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="grid three">
        @foreach ($tables as $table => $count)
            <article class="card kpi">
                <div class="kpi-label">{{ str_replace('_', ' ', $table) }}</div>
                <div class="kpi-value">{{ number_format($count, 0, ',', '.') }}</div>
            </article>
        @endforeach
    </div>
@endsection
