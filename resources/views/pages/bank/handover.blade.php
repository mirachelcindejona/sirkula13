@extends('layouts.app')
@section('title', 'Serah terima')
@section('heading', 'Serah terima reward')
@section('eyebrow', 'Operator')
@section('subtitle', 'Poin baru dipotong saat barang diserahkan.')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Warga</th><th>Reward</th><th>Poin</th><th></th></tr></thead>
            <tbody>
                @forelse ($redemptions as $redemption)
                    <tr>
                        <td class="font-bold">{{ $redemption->reference_code }}</td>
                        <td>{{ $redemption->residentProfile?->full_name }}</td>
                        <td>{{ $redemption->reward?->name }}</td>
                        <td>{{ number_format($redemption->points_cost, 0, ',', '.') }}</td>
                        <td>
                            <form method="POST" action="{{ route('bank.handover.store', $redemption) }}" data-confirm="Catat reward sudah diserahkan kepada {{ $redemption->residentProfile?->full_name }}?">
                                @csrf
                                <button class="btn primary">Serahkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Tidak ada reward yang menunggu diambil.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $redemptions->links() }}
    </div>
@endsection
