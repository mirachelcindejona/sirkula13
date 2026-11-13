@extends('layouts.app')
@section('title', 'Transaksi saya')
@section('heading', 'Pesanan saya')
@section('eyebrow', 'Belanja warga')
@section('subtitle', 'Pesanan yang Anda buat. Penjual menandai setelah barang diterima.')
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari kode atau nama produk" />
        <div class="table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Produk</th><th>Total</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="font-bold">{{ $order->reference_code }}</td>
                        <td>{{ $order->product?->name }}<br><span class="small muted">{{ $order->product?->code }}</span></td>
                        <td>{{ \App\Support\Labels::money($order->total_amount) }}</td>
                        <td>
                            <x-badge :status="$order->status" />
                            @if ($order->status === 'held_for_review' && $order->held_reason)
                                <div class="tiny muted">{{ $order->held_reason }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($approved && in_array($order->status, ['awaiting_seller', 'awaiting_buyer'], true))
                                @php($chat = \App\Support\OrderFlow::link($order))
                                @if ($chat)
                                    <a class="btn small-btn outline" href="{{ $chat }}" target="_blank" rel="noopener">Lanjut ke WhatsApp</a>
                                @endif
                                <form method="POST" action="{{ route('citizen.orders.cancel', $order) }}" data-confirm="Batalkan pesanan {{ $order->reference_code }}?">
                                    @csrf
                                    <button class="btn small-btn outline" type="submit">Batalkan</button>
                                </form>
                            @else
                                <span class="small muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $orders->links() }}
    </div>
@endsection
