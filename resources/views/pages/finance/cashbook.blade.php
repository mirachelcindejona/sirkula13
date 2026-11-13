@extends('layouts.app')
@section('title', 'Buku kas')
@section('heading', 'Buku kas')
@section('eyebrow', 'Kas aktual')
@section('subtitle', 'Catatan yang baru dibuat bisa diubah atau dihapus dalam 24 jam. Data yang lebih lama tetap tersimpan.')
@section('actions')
    <a class="btn primary" href="{{ route('finance.cashbook.create') }}">Catat kas</a>
@endsection
@section('content')
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($in) }}</div><div class="kpi-label">Kas masuk</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="receipt" /></div><div class="kpi-value">{{ \App\Support\Labels::money($out) }}</div><div class="kpi-label">Kas keluar</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="chart" /></div><div class="kpi-value">{{ \App\Support\Labels::money($in - $out) }}</div><div class="kpi-label">Saldo</div></div>
    </div>
    <h2 class="section-title">Buku kas lengkap</h2>
    <div class="card">
        <x-table-toolbar placeholder="Cari kode, kategori, atau keterangan" :filters="[['name' => 'direction', 'label' => 'Semua status', 'options' => ['in' => 'Pemasukan', 'out' => 'Pengeluaran']]]" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tanggal / kode</th><th>Jenis</th><th>Keterangan</th><th>Nominal</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td><strong>{{ $row->reference_code }}</strong><br><span class="small muted">{{ $row->transaction_at?->locale('id')->translatedFormat('d M Y') }}</span></td>
                            <td>{{ $row->category }}</td>
                            <td>{{ $row->description }}<br><span class="small muted">Bukti: {{ $row->proof_reference ?: '—' }}</span></td>
                            <td>{{ \App\Support\Labels::money($row->amount) }}</td>
                            <td><x-badge :status="$row->direction" /></td>
                            <td>
                                @if ($row->canRevise())
                                    <a class="btn small-btn outline" href="{{ route('finance.cashbook.edit', $row) }}">Ubah</a>
                                    <form method="POST" action="{{ route('finance.cashbook.destroy', $row) }}" data-confirm="Hapus catatan {{ $row->reference_code }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn small-btn danger" type="submit">Hapus</button>
                                    </form>
                                @else
                                    <span class="small muted">Terkunci</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada catatan kas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rows->links() }}
    </div>
@endsection
