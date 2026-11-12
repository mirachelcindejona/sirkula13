@extends('layouts.app')
@section('title', 'Pesanan')
@section('heading', 'Pesanan katalog')
@section('eyebrow', 'Program')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Produk</th><th>Pembeli</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="font-bold">{{ $order->reference_code }}</td>
                        <td>{{ $order->product?->name }}</td>
                        <td>{{ $order->buyer?->full_name ?: $order->external_buyer_name }}</td>
                        <td>{{ \App\Support\Labels::money($order->total_amount) }}</td>
                        <td><x-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>
@endsection
