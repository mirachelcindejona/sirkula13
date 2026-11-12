@extends('layouts.app')
@section('title', 'Laporan')
@section('heading', 'Laporan ringkas')
@section('eyebrow', 'Bendahara')
@section('content')
    <div class="grid gap-4 md:grid-cols-3">
        <article class="card p-5"><div class="kpi-label">Donasi terverifikasi</div><div class="kpi-value text-2xl">{{ \App\Support\Labels::money($donations) }}</div></article>
        <article class="card p-5"><div class="kpi-label">Pencairan dibayar</div><div class="kpi-value text-2xl">{{ \App\Support\Labels::money($payouts) }}</div></article>
        <article class="card p-5"><div class="kpi-label">Pengeluaran dibayar</div><div class="kpi-value text-2xl">{{ \App\Support\Labels::money($expenses) }}</div></article>
    </div>
@endsection
