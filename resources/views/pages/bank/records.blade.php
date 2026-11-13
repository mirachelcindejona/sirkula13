@extends('layouts.app')
@section('title', 'Catatan setoran')
@section('heading', 'Catatan setoran')
@section('eyebrow', 'Langsung tercatat')
@section('subtitle', 'Tidak ada antrean verifikasi. Poin dan tabungan mengikuti setoran yang diterima.')
@section('actions')
    <a class="btn primary" href="{{ route('bank.deposits.create') }}">Catat setoran</a>
@endsection
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari kode, nama, atau material" :filters="[['name' => 'type', 'label' => 'Semua jenis', 'options' => ['sale' => 'Jual', 'donation' => 'Donasi']]]" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode / waktu</th><th>Asal</th><th>Material</th><th>Jumlah</th><th>Pilihan</th><th>Uang ditabung</th><th>Poin</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($deposits as $deposit)
                        <tr>
                            <td><strong>{{ $deposit->reference_code }}</strong><br><span class="small muted">{{ $deposit->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</span></td>
                            <td>{{ $deposit->residentProfile?->full_name ?: ($deposit->external_name ?: '—') }}<br><span class="small muted">{{ $deposit->residentProfile?->resident_code ?: 'Luar RW' }}</span></td>
                            <td>{{ $deposit->material?->name }}</td>
                            <td>{{ \App\Support\Labels::quantity($deposit->weight_kg, $deposit->unit_snapshot ?: $deposit->material?->unit) }}</td>
                            <td><x-badge :status="$deposit->type" /></td>
                            <td>{{ \App\Support\Labels::money($deposit->money_amount) }}</td>
                            <td>{{ number_format($deposit->eco_points_awarded, 0, ',', '.') }}</td>
                            <td><x-badge :status="$deposit->accepted ? 'accepted' : 'rejected'" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="8">Belum ada setoran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $deposits->links() }}
    </div>
@endsection
