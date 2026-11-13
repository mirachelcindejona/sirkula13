@extends('layouts.app')
@section('title', 'Pesanan ditahan')
@section('heading', 'Pesanan ditahan')
@section('eyebrow', 'Pemeriksaan pengelola')
@section('subtitle', 'Pesanan ini menunggu pemeriksaan. Alasan tampil di sini, pelepasan dilakukan dari menu pesanan.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari kode, produk, atau pembeli" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Produk</th><th>Pembeli</th><th>Alasan</th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><strong>{{ $order->reference_code }}</strong></td>
                            <td>{{ $order->product?->name }}</td>
                            <td>{{ $order->buyer?->full_name }}</td>
                            <td>{{ $order->held_reason ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada pesanan yang ditahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
@endsection
