@extends('layouts.app')
@section('title', 'Tarif rupiah & EcoPoints')
@section('heading', 'Tarif rupiah & EcoPoints')
@section('eyebrow', 'Harga rupiah harus disahkan RW')
@section('subtitle', 'Atur harga beli per kilogram dan tarif EcoPoints yang berlaku. Riwayat transaksi tetap memakai tarif saat transaksi dicatat.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.readiness') }}"><x-icon name="clipboard" /> Kesiapan operasional</a>
@endsection
@section('content')
    <div class="callout warning"><x-icon name="alert" /><div><strong>Tarif menunggu pengesahan RW</strong>Harga perlu disepakati pengurus RW sebelum digunakan untuk pembayaran setoran jual.</div></div>
    <div class="card gap-top">
        <div class="card-head">
            <div>
                <h3 class="card-title">Harga beli material dan EcoPoints</h3>
                <p class="card-subtitle">Jenis material dan reward dikelola dari sini, terpisah dari produk yang dijual warga.</p>
            </div>
            <div class="row">
                <a class="btn secondary" href="{{ route('admin.materials.index') }}">Jenis material</a>
                <a class="btn outline" href="{{ route('admin.rewards.index') }}">Reward</a>
            </div>
        </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Material</th><th>Harga/kg</th><th>Poin/kg</th><th>Berlaku</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($rates as $rate)
                    <tr>
                        <td class="font-bold">{{ $rate->material?->name }}</td>
                        <td>{{ \App\Support\Labels::money($rate->buy_price_per_kg) }}</td>
                        <td>{{ $rate->eco_points_per_kg }}</td>
                        <td>{{ $rate->effective_from }}</td>
                        <td><x-badge :status="$rate->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
        {{ $rates->links() }}
    </div>
@endsection
