@extends('layouts.app')
@section('title', 'Pesanan produk')
@section('heading', 'Pesanan produk')
@section('eyebrow', 'Pengelola kegiatan dan superadmin')
@section('subtitle', 'Tahan pesanan yang perlu diperiksa. Pesanan yang sudah jelas bisa dilepas kembali.')
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari kode, produk, atau pembeli" :filters="[['name' => 'status', 'label' => 'Semua status', 'options' => ['awaiting_seller' => 'Menunggu penjual', 'held_for_review' => 'Ditahan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan']]]" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Produk</th><th>Pembeli</th><th>Total</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><strong>{{ $order->reference_code }}</strong><br><span class="small muted">{{ $order->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</span></td>
                            <td>{{ $order->product?->name }}<br><span class="small muted">{{ $order->quantity }} barang</span></td>
                            <td>{{ $order->buyer?->full_name ?: ($order->external_buyer_name ?: '—') }}</td>
                            <td>{{ \App\Support\Labels::money($order->total_amount) }}</td>
                            <td>
                                <x-badge :status="$order->status" />
                                @if ($order->status === 'held_for_review' && $order->held_reason)<div class="tiny muted">{{ $order->held_reason }}</div>@endif
                            </td>
                            <td>
                                @if ($order->status === 'held_for_review')
                                    <form method="POST" action="{{ route('program.orders.release', $order) }}" data-confirm="Lepas pemeriksaan pesanan {{ $order->reference_code }}?">
                                        @csrf
                                        <button class="btn small-btn secondary" type="submit">Lepas pemeriksaan</button>
                                    </form>
                                @elseif (! in_array($order->status, ['completed', 'cancelled'], true))
                                    <button class="btn small-btn danger" type="button" data-hold-open data-action="{{ route('program.orders.hold', $order) }}" data-code="{{ $order->reference_code }}">Tahan untuk pemeriksaan</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
    <dialog id="hold-dialog" class="confirm-dialog">
        <form method="POST" id="hold-form">
            @csrf
            <h3 id="hold-title">Tahan pesanan</h3>
            <p class="small muted">Pesanan ditahan sampai pemeriksaan dilepas.</p>
            <div class="field gap-top">
                <label for="hold-reason">Alasan ditahan <span class="required">*</span></label>
                <textarea id="hold-reason" name="reason" required maxlength="500" placeholder="Tuliskan alasan yang jelas"></textarea>
            </div>
            <div class="form-actions">
                <button class="btn outline" type="button" id="hold-cancel">Batal</button>
                <button class="btn danger" type="submit">Tahan pesanan</button>
            </div>
        </form>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('hold-dialog');
            const form = document.getElementById('hold-form');
            const title = document.getElementById('hold-title');
            document.querySelectorAll('[data-hold-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    form.action = button.dataset.action;
                    title.textContent = 'Tahan pesanan ' + button.dataset.code;
                    form.querySelector('[name="reason"]').value = '';
                    dialog.showModal();
                });
            });
            document.getElementById('hold-cancel').addEventListener('click', () => dialog.close());
        })();
    </script>
@endsection
