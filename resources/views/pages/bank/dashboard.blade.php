@extends('layouts.app')
@section('title', 'Ringkasan operator')
@section('heading', 'Ringkasan operator')
@section('eyebrow', 'Bank sampah')
@section('subtitle', 'Setoran tercatat langsung. Uang warga ditabung sampai jadwal pencairan.')
@section('content')
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="scale" /></div><div class="kpi-value">{{ $today }}</div><div class="kpi-label">Setoran hari ini</div><div class="kpi-note">Sudah masuk catatan</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="box" /></div><div class="kpi-value">{{ \App\Support\Labels::kg($stockKg) }}</div><div class="kpi-label">Stok tersedia</div><div class="kpi-note">Setoran diterima dikurangi keluar</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($savedMoney) }}</div><div class="kpi-label">Tabungan belum cair</div><div class="kpi-note">Dibagikan saat jadwal pencairan</div></div>
    </div>
    <div class="row between"><h2 class="section-title">Setoran terbaru</h2><a class="text-link" href="{{ route('bank.records') }}">Lihat semua</a></div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Warga / asal</th><th>Material</th><th>Berat</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($latest as $deposit)
                        <tr>
                            <td><strong>{{ $deposit->reference_code }}</strong></td>
                            <td>{{ $deposit->residentProfile?->full_name ?: ($deposit->external_name ?: '—') }}</td>
                            <td>{{ $deposit->material?->name }}</td>
                            <td>{{ \App\Support\Labels::quantity($deposit->weight_kg, $deposit->unit_snapshot ?: $deposit->material?->unit) }}</td>
                            <td><x-badge :status="$deposit->accepted ? 'accepted' : 'rejected'" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada setoran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <h2 class="section-title">Akses cepat</h2>
    <div class="grid three">
        <div class="card"><div class="kpi-icon"><x-icon name="scale" /></div><h3 class="card-title">Catat setoran</h3><p class="card-subtitle">Warga RW 13 atau penyetor dari luar.</p><a class="btn secondary block gap-top" href="{{ route('bank.deposits.create') }}">Mulai penimbangan</a></div>
        <div class="card"><div class="kpi-icon"><x-icon name="box" /></div><h3 class="card-title">Stok & penyaluran</h3><p class="card-subtitle">Material keluar tidak boleh melebihi stok.</p><a class="btn secondary block gap-top" href="{{ route('bank.inventory') }}">Kelola stok</a></div>
        <div class="card"><div class="kpi-icon"><x-icon name="wallet" /></div><h3 class="card-title">Pencairan setoran</h3><p class="card-subtitle">Bagikan tabungan saat jadwal pembagian uang.</p><a class="btn secondary block gap-top" href="{{ route('finance.payouts') }}">Buka pencairan</a></div>
    </div>
@endsection
