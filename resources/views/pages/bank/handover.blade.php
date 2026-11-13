@extends('layouts.app')
@section('title', 'Serah terima reward')
@section('heading', 'Serah terima reward')
@section('eyebrow', 'Pastikan orangnya benar')
@section('subtitle', 'Poin baru dipotong setelah reward benar-benar diserahkan. Pembatalan mengembalikan poin ke saldo.')
@section('content')
    <div class="callout"><x-icon name="gift" /><div><strong>Cocokkan nama dan ID warga</strong>Stok yang dipesan masih ditahan. Serahkan hanya kepada warga yang menukar.</div></div>
    <div class="card gap-top">
        <x-table-toolbar placeholder="Cari kode, warga, atau reward" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Warga</th><th>Reward</th><th>Poin ditahan</th><th>Dibuat</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($redemptions as $redemption)
                        <tr>
                            <td><strong>{{ $redemption->reference_code }}</strong></td>
                            <td>{{ $redemption->residentProfile?->full_name }}<br><span class="small muted">{{ $redemption->residentProfile?->resident_code }}</span></td>
                            <td>{{ $redemption->reward?->name }}</td>
                            <td>{{ number_format($redemption->points_cost, 0, ',', '.') }}</td>
                            <td>{{ $redemption->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</td>
                            <td>
                                <form method="POST" action="{{ route('bank.handover.store', $redemption) }}" data-confirm="Reward sudah diserahkan kepada {{ $redemption->residentProfile?->full_name }}?">
                                    @csrf
                                    <button class="btn small-btn primary" type="submit">Sudah diserahkan</button>
                                </form>
                                <form method="POST" action="{{ route('bank.handover.cancel', $redemption) }}" data-confirm="Batalkan penukaran ini? Poin kembali bisa dipakai.">
                                    @csrf
                                    <button class="btn small-btn danger" type="submit">Batalkan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Tidak ada reward yang menunggu serah terima.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $redemptions->links() }}
    </div>
@endsection
