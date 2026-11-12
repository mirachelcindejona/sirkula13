@extends('layouts.app')
@section('title', 'Bank sampah')
@section('heading', 'Bank sampah')
@section('eyebrow', 'Operator')
@section('actions')
    <a href="{{ route('bank.deposits.create') }}" class="btn primary">Catat setoran</a>
@endsection
@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <article class="card p-4"><div class="kpi-label">Menunggu verifikasi</div><div class="kpi-value">{{ $submitted }}</div></article>
        <article class="card p-4"><div class="kpi-label">Diverifikasi hari ini</div><div class="kpi-value">{{ $verifiedToday }}</div></article>
        <article class="card p-4"><div class="kpi-label">Perkiraan stok</div><div class="kpi-value">{{ \App\Support\Labels::kg($stockKg) }}</div></article>
    </div>
    <div class="card mt-5 overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Warga</th><th>Material</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($latest as $deposit)
                    <tr>
                        <td class="font-bold">{{ $deposit->reference_code }}</td>
                        <td>{{ $deposit->residentProfile?->full_name ?: '—' }}</td>
                        <td>{{ $deposit->material?->name }}</td>
                        <td><x-badge :status="$deposit->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
