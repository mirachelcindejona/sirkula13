@extends('layouts.app')
@section('title', 'Transaksi saya')
@section('heading', 'Transaksi katalog')
@section('eyebrow', 'Warga')
@section('subtitle', 'Pesanan produk warga, terpisah dari penukaran reward.')
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari kode atau nama produk" />
        <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Produk</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="font-bold">{{ $order->reference_code }}</td>
                        <td>{{ $order->product?->name }}</td>
                        <td>{{ \App\Support\Labels::money($order->total_amount) }}</td>
                        <td><x-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $orders->links() }}
    </div>
@endsection
