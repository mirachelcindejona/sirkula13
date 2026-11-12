@extends('layouts.app')
@section('title', 'Pencairan')
@section('heading', 'Pencairan setoran jual')
@section('eyebrow', 'Bendahara')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Nominal</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($payouts as $payout)
                    <tr>
                        <td class="font-bold">{{ $payout->reference_code }}</td>
                        <td>{{ \App\Support\Labels::money($payout->amount) }}</td>
                        <td><x-badge :status="$payout->status" /></td>
                        <td>
                            @if ($payout->status !== 'paid')
                                <form method="POST" action="{{ route('finance.payouts.pay', $payout) }}" data-confirm="Catat pembayaran ini sebagai lunas? Lakukan hanya jika uang sudah benar-benar dibayarkan.">@csrf<button class="btn primary">Tandai dibayar</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada pencairan.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $payouts->links() }}
    </div>
@endsection
