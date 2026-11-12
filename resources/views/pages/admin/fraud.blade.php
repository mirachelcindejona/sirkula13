@extends('layouts.app')
@section('title', 'Pemeriksaan transaksi berisiko')
@section('heading', 'Pemeriksaan transaksi berisiko')
@section('eyebrow', 'Tahan poin sampai jelas')
@section('subtitle', 'Transaksi yang mencurigakan perlu bukti dan keputusan yang tercatat.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Warga</th><th>Material</th><th>Catatan</th></tr></thead>
                <tbody>
                    @forelse ($deposits as $deposit)
                        <tr>
                            <td><strong>{{ $deposit->reference_code }}</strong></td>
                            <td>{{ $deposit->residentProfile?->full_name }}</td>
                            <td>{{ $deposit->material?->name }}</td>
                            <td>{{ $deposit->note ?: $deposit->quality_note }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada transaksi yang ditahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $deposits->links() }}
    </div>
@endsection
