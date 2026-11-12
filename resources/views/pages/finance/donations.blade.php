@extends('layouts.app')
@section('title', 'Donasi')
@section('heading', 'Donasi uang')
@section('eyebrow', 'Bendahara')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Donatur</th><th>Tujuan</th><th>Nominal</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($donations as $donation)
                    <tr>
                        <td class="font-bold">{{ $donation->reference_code }}</td>
                        <td>{{ $donation->alias_snapshot ?: $donation->donor_name_snapshot }}</td>
                        <td>{{ $donation->purpose }}</td>
                        <td>{{ \App\Support\Labels::money($donation->amount) }}</td>
                        <td><x-badge :status="$donation->status" /></td>
                        <td>
                            @if ($donation->status !== 'verified')
                                <form method="POST" action="{{ route('finance.donations.verify', $donation) }}" data-confirm="Konfirmasi donasi ini sudah diterima? Kas dan SupportPoints akan bertambah.">@csrf<button class="btn primary">Verifikasi</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada donasi.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $donations->links() }}
    </div>
@endsection
