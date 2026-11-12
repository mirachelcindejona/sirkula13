@extends('layouts.app')
@section('title', 'Dashboard program')
@section('heading', 'Dashboard program')
@section('eyebrow', 'Kelola kegiatan dan partisipasi warga')
@section('subtitle', 'Pantau pendaftaran, kehadiran yang menunggu verifikasi, katalog, dan transaksi produk.')
@section('content')
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="calendar" /></div><div class="kpi-value">{{ $publishedEvents }}</div><div class="kpi-label">Kegiatan tayang</div><div class="kpi-note">Terlihat oleh warga</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="check" /></div><div class="kpi-value">{{ $pendingAttendance }}</div><div class="kpi-label">Kehadiran menunggu</div><div class="kpi-note">Perlu verifikator berbeda</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="book" /></div><div class="kpi-value">{{ $drafts }}</div><div class="kpi-label">Draft menunggu</div><div class="kpi-note">Kegiatan atau produk</div></div>
    </div>
    <div class="grid two gap-top">
        <div class="card">
            <h3 class="card-title">Buat dan kelola program</h3>
            <p class="card-subtitle">Draft belum terlihat oleh warga sampai ditinjau Superadmin.</p>
            <div class="stack gap-top">
                <a class="btn primary block" href="{{ route('program.events') }}">Buat kegiatan / verifikasi kehadiran</a>
                <a class="btn outline block" href="{{ route('program.catalog') }}">Tambah produk ke katalog</a>
                <a class="btn outline block" href="{{ route('program.orders') }}">Kelola referensi transaksi produk</a>
            </div>
        </div>
        <div class="card">
            <h3 class="card-title">Ketentuan poin kegiatan</h3>
            <div class="list-row"><div class="list-icon"><x-icon name="calendar" /></div><div class="grow"><h4>Kehadiran terverifikasi</h4><p>50 EcoPoints untuk kegiatan warga yang diikuti dan disahkan.</p></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="award" /></div><div class="grow"><h4>Tugas tambahan</h4><p>Tambahan 50 poin hanya jika tugas berbeda dari kehadiran, sudah dilaksanakan, dan disahkan.</p></div></div>
            <div class="callout warning gap-top"><x-icon name="shield" /><div><strong>Anti klaim ganda</strong>Registrasi bukan bukti kehadiran. Klaim sendiri tidak boleh disahkan oleh orang yang sama.</div></div>
        </div>
    </div>
@endsection
