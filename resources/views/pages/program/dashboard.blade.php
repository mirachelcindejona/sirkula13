@extends('layouts.app')
@section('title', 'Dashboard program')
@section('heading', 'Dashboard program')
@section('eyebrow', 'Kelola kegiatan dan partisipasi warga')
@section('subtitle', 'Pantau pendaftaran, kehadiran yang menunggu verifikasi, katalog, dan transaksi produk.')
@section('content')
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="calendar" /></div><div class="kpi-value">{{ $publishedEvents }}</div><div class="kpi-label">Kegiatan tayang</div><div class="kpi-note">Terlihat oleh warga</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="box" /></div><div class="kpi-value">{{ $products }}</div><div class="kpi-label">Produk tayang</div><div class="kpi-note">Bisa dipesan warga</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="book" /></div><div class="kpi-value">{{ $drafts }}</div><div class="kpi-label">Masih draft</div><div class="kpi-note">Belum terlihat warga</div></div>
    </div>
    <div class="grid two gap-top">
        <div class="card">
            <h3 class="card-title">Buat dan kelola program</h3>
            <p class="card-subtitle">Pilih tayangkan agar langsung terlihat warga, atau simpan draft.</p>
            <div class="stack gap-top">
                <a class="btn primary block" href="{{ route('program.events.create') }}">Tambah kegiatan</a>
                <a class="btn outline block" href="{{ route('program.events') }}">Catat kehadiran</a>
                <a class="btn outline block" href="{{ route('program.catalog.create') }}">Tambah produk pengurus</a>
                <a class="btn outline block" href="{{ route('program.orders') }}">Pantau pesanan</a>
            </div>
        </div>
        <div class="card">
            <h3 class="card-title">Ketentuan poin kegiatan</h3>
            <div class="list-row"><div class="list-icon"><x-icon name="calendar" /></div><div class="grow"><h4>Kehadiran terverifikasi</h4><p>50 EcoPoints untuk kegiatan warga yang diikuti dan disahkan.</p></div></div>
            <div class="callout gap-top"><x-icon name="check" /><div><strong>Checklist sekali simpan</strong>Centang banyak warga lalu simpan. Poin kehadiran masuk pada saat yang sama.</div></div>
        </div>
    </div>
@endsection
