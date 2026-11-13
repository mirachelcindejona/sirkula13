@extends('layouts.app')
@section('title', 'Ringkasan keuangan')
@section('heading', 'Ringkasan keuangan')
@section('eyebrow', 'Bendahara')
@section('subtitle', 'Kas hanya berubah ketika uang benar-benar diterima atau dibayarkan.')
@section('content')
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($balance) }}</div><div class="kpi-label">Saldo kas aktual</div><div class="kpi-note">Kas masuk dikurangi kas keluar</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="receipt" /></div><div class="kpi-value">{{ \App\Support\Labels::money($due) }}</div><div class="kpi-label">Tabungan warga</div><div class="kpi-note">Belum dicairkan</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="heart" /></div><div class="kpi-value">{{ \App\Support\Labels::money($awaitingDonations) }}</div><div class="kpi-label">Donasi belum diterima</div><div class="kpi-note">Belum masuk kas</div></div>
    </div>
    <div class="grid two gap-top">
        <div class="card">
            <h3 class="card-title">Jalankan pembukuan</h3>
            <div class="stack gap-top">
                <a class="btn primary block" href="{{ route('finance.payouts') }}">Kelola pencairan setoran</a>
                <a class="btn secondary block" href="{{ route('finance.donations') }}">Verifikasi donasi uang</a>
                <a class="btn outline block" href="{{ route('finance.cashbook') }}">Buka buku kas</a>
                <a class="btn outline block" href="{{ route('finance.reports') }}">Lihat laporan</a>
            </div>
        </div>
        <div class="card">
            <h3 class="card-title">Prinsip buku kas</h3>
            <div class="flow-step"><div class="step-number">1</div><div><h4>Setoran material bukan kas</h4><p>Stok bertambah saat setoran dicatat. Uang warga ditabung dulu.</p></div></div>
            <div class="flow-step"><div class="step-number">2</div><div><h4>Donasi perlu konfirmasi</h4><p>SupportPoints diberikan setelah uang diterima.</p></div></div>
            <div class="flow-step"><div class="step-number">3</div><div><h4>Catatan langsung masuk</h4><p>Pemasukan dan pengeluaran yang sudah terjadi tidak menunggu persetujuan lagi.</p></div></div>
        </div>
    </div>
@endsection
