@extends('layouts.app')
@section('title', 'Setoran saya')
@section('heading', 'Setoran saya')
@section('eyebrow', 'Hasil timbang dan tabungan')
@section('subtitle', 'Semua barang yang pernah Anda setor, berikut poin dan uangnya.')
@section('content')
    @php
        $earned = $earned ?? 0;
        $amountDue = $amountDue ?? 0;
    @endphp
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="scale" /></div><div class="kpi-value">{{ $deposits->count() }}</div><div class="kpi-label">Kali setor</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="sparkles" /></div><div class="kpi-value">{{ number_format($earned, 0, ',', '.') }}</div><div class="kpi-label">Poin dari setoran</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($amountDue) }}</div><div class="kpi-label">Uang yang belum diambil</div><div class="kpi-note">Diberikan saat jadwal pembagian</div></div>
    </div>
    <h2 class="section-title">Riwayat</h2>
    <div class="card">
        <x-table-toolbar placeholder="Cari barang atau kode" :filters="[['name' => 'type', 'label' => 'Semua jenis', 'options' => ['sale' => 'Jual', 'donation' => 'Donasi']]]" />
        @if ($deposits->isEmpty())
            <div class="empty-state"><div class="empty-icon"><x-icon name="scale" /></div><h3>Belum ada setoran</h3><p>Bawa barang bekas yang sudah dipilah ke bank sampah RW. Setelah ditimbang petugas, catatannya muncul di sini.</p><a class="btn outline" href="{{ route('public.guide') }}">Lihat cara memilah</a></div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tanggal</th><th>Barang</th><th>Jumlah</th><th>Pilihan</th><th>Uang</th><th>Poin</th><th>Hasil</th></tr></thead>
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
