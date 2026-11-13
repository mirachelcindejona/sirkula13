@extends('layouts.app')
@section('title', 'Laporan')
@section('heading', 'Laporan')
@section('eyebrow', 'Ringkasan yang bisa diunduh')
@section('actions')
    <a class="btn primary" href="{{ route('finance.reports.export') }}">Ekspor Excel</a>
@endsection
@section('content')
    <div class="grid four">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="user" /></div><div class="kpi-value">{{ $activeResidents }}</div><div class="kpi-label">Warga aktif</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="scale" /></div><div class="kpi-value">{{ $deposits }}</div><div class="kpi-label">Setoran tercatat</div><div class="kpi-note">{{ \App\Support\Labels::kg($soldKg) }} jual · {{ \App\Support\Labels::kg($donatedKg) }} donasi</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="box" /></div><div class="kpi-value">{{ \App\Support\Labels::kg($outgoingKg) }}</div><div class="kpi-label">Material keluar</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($balance) }}</div><div class="kpi-label">Saldo kas</div></div>
    </div>
    <h2 class="section-title">Ringkasan keuangan</h2>
    <div class="grid two">
        <div class="card">
            <h3 class="card-title">Kas masuk dan keluar</h3>
            @php($max = max(1, $in, $out, abs($balance)))
            <div class="chart-bars">
                <div class="chart-bar"><b>{{ \App\Support\Labels::money($in) }}</b><div class="bar" style="height:{{ max(8, ($in / $max) * 100) }}%"></div><small>Masuk</small></div>
                <div class="chart-bar"><b>{{ \App\Support\Labels::money($out) }}</b><div class="bar" style="height:{{ max(8, ($out / $max) * 100) }}%;background:linear-gradient(180deg,var(--orange),#D99A38)"></div><small>Keluar</small></div>
                <div class="chart-bar"><b>{{ \App\Support\Labels::money($balance) }}</b><div class="bar" style="height:{{ max(8, (abs($balance) / $max) * 100) }}%;background:linear-gradient(180deg,var(--yellow),#D9A500)"></div><small>Saldo</small></div>
            </div>
        </div>
        <div class="card">
            <h3 class="card-title">Yang belum selesai</h3>
            <div class="list-row"><div class="list-icon"><x-icon name="wallet" /></div><div class="grow"><h4>Tabungan belum cair</h4><p>Menunggu jadwal pembagian</p></div><strong>{{ \App\Support\Labels::money($due) }}</strong></div>
            <div class="list-row"><div class="list-icon"><x-icon name="heart" /></div><div class="grow"><h4>Donasi belum diverifikasi</h4><p>Belum menjadi kas</p></div><strong>{{ \App\Support\Labels::money($awaitingDonations) }}</strong></div>
        </div>
    </div>
    <h2 class="section-title">Partisipasi kegiatan</h2>
    <div class="card">
        @forelse ($events as $event)
            @php($width = $events->max('attendances_count') ? ($event->attendances_count / max(1, $events->max('attendances_count'))) * 100 : 0)
            <div class="list-row">
                <div class="list-icon"><x-icon name="calendar" /></div>
                <div class="grow"><h4>{{ $event->title }}</h4><div class="progress-line gap-top"><span style="width:{{ $width }}%"></span></div></div>
                <strong>{{ $event->attendances_count }} hadir</strong>
            </div>
        @empty
            <p class="small muted">Belum ada kegiatan tayang.</p>
        @endforelse
    </div>
@endsection
