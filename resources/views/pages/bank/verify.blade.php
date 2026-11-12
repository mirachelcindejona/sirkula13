@extends('layouts.app')
@section('title', 'Verifikasi setoran')
@section('heading', 'Verifikasi setoran')
@section('eyebrow', 'Operator')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Warga</th><th>Material</th><th>Berat</th><th>Poin</th><th></th></tr></thead>
            <tbody>
                @forelse ($deposits as $deposit)
                    <tr>
                        <td class="font-bold">{{ $deposit->reference_code }}</td>
                        <td>{{ $deposit->residentProfile?->full_name }}</td>
                        <td>{{ $deposit->material?->name }}</td>
                        <td>{{ \App\Support\Labels::kg($deposit->weight_kg) }}</td>
                        <td>{{ number_format($deposit->eco_points_awarded, 0, ',', '.') }}</td>
                        <td>
                            <form method="POST" action="{{ route('bank.verify.store', $deposit) }}" data-confirm="Sahkan setoran {{ $deposit->reference_code }}? Poin dan stok akan diperbarui.">
                                @csrf
                                <button class="btn primary">Verifikasi</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Tidak ada setoran yang menunggu.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $deposits->links() }}
    </div>
@endsection
