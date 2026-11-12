@extends('layouts.app')
@section('title', 'Riwayat setoran')
@section('heading', 'Riwayat setoran')
@section('eyebrow', 'Periksa hasil setoran dan pembayaran')
@section('subtitle', 'Lihat jenis material, berat aktual, pilihan jual/donasi, poin, nilai rupiah, dan status pembayaran.')
@section('content')
    @php
        $earned = $earned ?? 0;
        $amountDue = $amountDue ?? 0;
    @endphp
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="scale" /></div><div class="kpi-value">{{ $deposits->count() }}</div><div class="kpi-label">Riwayat setoran</div><div class="kpi-note">Termasuk transaksi sebelumnya</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="sparkles" /></div><div class="kpi-value">{{ number_format($earned, 0, ',', '.') }}</div><div class="kpi-label">EcoPoints dari setoran</div><div class="kpi-note">Hanya yang diverifikasi</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($amountDue) }}</div><div class="kpi-label">Pembayaran belum lunas</div><div class="kpi-note">Tidak termasuk donasi material</div></div>
    </div>
    <div class="callout gap-top"><x-icon name="receipt" /><div><strong>Periksa status satu per satu</strong>“Menunggu verifikasi” berarti stok dan poin belum ditambahkan. “Menunggu pembayaran” berarti setoran Jual sudah terverifikasi tetapi uang belum dicatat sebagai dibayarkan. Donasi material tidak menghasilkan pembayaran rupiah.</div></div>
    <h2 class="section-title">Riwayat setoran saya</h2>
    <div class="card">
        @if ($deposits->isEmpty())
            <div class="empty-state"><div class="empty-icon"><x-icon name="scale" /></div><h3>Belum ada setoran</h3><p>Ketika Anda menyetor, petugas akan mencatat hasil timbang dan pilihan transaksi. Riwayat tampil di sini.</p></div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID / Tanggal</th><th>Material</th><th>Berat</th><th>Pilihan</th><th>Nilai rupiah</th><th>EcoPoints</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($deposits as $deposit)
                            <tr>
                                <td><strong>{{ $deposit->reference_code }}</strong><br><span class="small muted">{{ $deposit->created_at?->locale('id')->translatedFormat('d M Y HH:mm') }}</span></td>
                                <td>{{ $deposit->material?->short_name ?: $deposit->material?->name }}</td>
                                <td>{{ number_format((float) $deposit->weight_kg, 2, ',', '.') }} kg</td>
                                <td><x-badge :status="$deposit->type" /></td>
                                <td>
                                    {{ $deposit->type === 'donation' ? '—' : \App\Support\Labels::money($deposit->money_amount) }}
                                    <br>
                                    <span class="small muted">
                                        @if ($deposit->type === 'donation')
                                            Tidak dibayar
                                        @elseif ($deposit->payout?->status === 'paid')
                                            Lunas
                                        @elseif ($deposit->payout?->status === 'approved')
                                            Disetujui, menunggu pembayaran
                                        @elseif ($deposit->payout)
                                            Menunggu persetujuan
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $deposit->status === 'verified' ? number_format($deposit->eco_points_awarded, 0, ',', '.') : 'Belum masuk' }}</td>
                                <td><x-badge :status="$deposit->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{ $deposits->links() }}
    </div>
@endsection
