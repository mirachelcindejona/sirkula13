@extends('layouts.app')
@section('title', 'Dashboard administrasi')
@section('heading', 'Dashboard administrasi')
@section('eyebrow', 'Kontrol dan kesiapan layanan')
@section('subtitle', 'Kelola akses sesuai penugasan, periksa antrean dan audit, serta pastikan keputusan operasional sebelum mulai digunakan.')
@section('content')
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="user" /></div><div class="kpi-value">{{ $queue }}</div><div class="kpi-label">Pendaftaran menunggu</div><div class="kpi-note">Akun warga yang belum selesai</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="alert" /></div><div class="kpi-value">{{ $held }}</div><div class="kpi-label">Pesanan ditahan</div><div class="kpi-note">Menunggu pemeriksaan</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($cashBalance) }}</div><div class="kpi-label">Saldo kas</div><div class="kpi-note">Berdasarkan buku kas</div></div>
    </div>
    <div class="grid two gap-top">
        <div class="card">
            <h3 class="card-title">Akses cepat</h3>
            <div class="stack gap-top">
                <a class="btn primary block" href="{{ route('admin.approvals') }}"><x-icon name="user" /> Periksa pendaftaran warga</a>
                <a class="btn secondary block" href="{{ route('finance.reports') }}"><x-icon name="chart" /> Lihat laporan</a>
                <a class="btn outline block" href="{{ route('finance.channel') }}"><x-icon name="heart" /> Rekening dan QRIS donasi</a>
                <a class="btn outline block" href="{{ route('admin.fraud') }}"><x-icon name="alert" /> Periksa transaksi tertahan</a>
                <a class="btn outline block" href="{{ route('admin.audit') }}"><x-icon name="history" /> Riwayat aktivitas</a>
            </div>
        </div>
        <div class="card">
            <h3 class="card-title">Kontrol penting</h3>
            @foreach ([
                ['Akun memakai role yang ditetapkan', 'Role & akses'],
                ['Tarif memiliki tanggal berlaku', 'Tarif & konfigurasi'],
                ['Transaksi final dikoreksi lewat catatan baru', 'Audit log'],
                ['Poin setoran masuk saat diterima', 'Kegiatan tetap menunggu pengesahan hadir'],
                ['Data sensitif tidak ditampilkan ke publik', 'Laporan dan leaderboard'],
            ] as [$title, $note])
                <div class="list-row">
                    <div class="list-icon"><x-icon name="shield" /></div>
                    <div class="grow"><h4>{{ $title }}</h4><p>{{ $note }}</p></div>
                    <span class="badge success">Aktif</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
