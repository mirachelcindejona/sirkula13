@extends('layouts.app')
@section('title', 'Riwayat setoran')
@section('heading', 'Riwayat setoran')
@section('eyebrow', 'Periksa hasil setoran dan pembayaran')
@section('subtitle', 'Lihat jenis material, jumlah aktual, pilihan jual atau donasi, poin, dan tabungan.')
@section('content')
    @php
        $earned = $earned ?? 0;
        $amountDue = $amountDue ?? 0;
    @endphp
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="scale" /></div><div class="kpi-value">{{ $deposits->count() }}</div><div class="kpi-label">Riwayat setoran</div><div class="kpi-note">Termasuk transaksi sebelumnya</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="sparkles" /></div><div class="kpi-value">{{ number_format($earned, 0, ',', '.') }}</div><div class="kpi-label">EcoPoints dari setoran</div><div class="kpi-note">Hanya material yang diterima</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($amountDue) }}</div><div class="kpi-label">Tabungan belum cair</div><div class="kpi-note">Tidak termasuk donasi material</div></div>
    </div>
    <div class="callout gap-top"><x-icon name="receipt" /><div><strong>Setoran langsung tercatat</strong>Material yang diterima langsung mendapat poin. Uang jual ditabung dan dicairkan sesuai jadwal. Material yang ditolak tidak mendapat poin atau uang.</div></div>
    <h2 class="section-title">Riwayat setoran saya</h2>
    <div class="card">
        <x-table-toolbar placeholder="Cari kode atau material" :filters="[['name' => 'type', 'label' => 'Semua jenis', 'options' => ['sale' => 'Jual', 'donation' => 'Donasi']]]" />
        @if ($deposits->isEmpty())
            <div class="empty-state"><div class="empty-icon"><x-icon name="scale" /></div><h3>Belum ada setoran</h3><p>Ketika Anda menyetor, petugas akan mencatat hasil timbang dan pilihan transaksi. Riwayat tampil di sini.</p></div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID / Tanggal</th><th>Material</th><th>Jumlah</th><th>Pilihan</th><th>Nilai rupiah</th><th>EcoPoints</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($deposits as $deposit)
                            <tr>
                                <td><strong>{{ $deposit->reference_code }}</strong><br><span class="small muted">{{ $deposit->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</span></td>
                                <td>{{ $deposit->material?->short_name ?: $deposit->material?->name }}</td>
                                <td>{{ \App\Support\Labels::quantity($deposit->weight_kg, $deposit->unit_snapshot ?: $deposit->material?->unit) }}</td>
                                <td><x-badge :status="$deposit->type" /></td>
                                <td>
                                    {{ $deposit->type === 'donation' ? '—' : \App\Support\Labels::money($deposit->money_amount) }}
                                    <br>
                                    <span class="small muted">
                                        @if ($deposit->type === 'donation' || ! $deposit->accepted)
                                            Tidak dibayar
                                        @else
                                            {{ \App\Support\Labels::status($deposit->payout?->displayStatus()) }}
                                        @endif
                                    </span>
                                </td>
                                <td>{{ number_format($deposit->eco_points_awarded, 0, ',', '.') }}</td>
                                <td><x-badge :status="$deposit->accepted ? 'accepted' : 'rejected'" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{ $deposits->links() }}
    </div>
@endsection
