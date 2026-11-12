@extends('layouts.app')
@section('title', 'Persetujuan keuangan')
@section('heading', 'Persetujuan keuangan')
@section('eyebrow', 'Kas yang perlu diperiksa')
@section('subtitle', 'Periksa arah, kategori, dan nominal sebelum kas dianggap final.')
@section('actions')
    <a class="btn outline" href="{{ route('finance.cashbook') }}">Buka buku kas</a>
@endsection
@section('content')
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Arah</th><th>Kategori</th><th>Nominal</th></tr></thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->reference_code }}</td>
                            <td>{{ $row->direction === 'in' ? 'Masuk' : 'Keluar' }}</td>
                            <td>{{ $row->category }}</td>
                            <td>{{ \App\Support\Labels::money($row->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada catatan kas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rows->links() }}
    </div>
@endsection
