@extends('layouts.app')
@section('title', 'Keuangan')
@section('heading', 'Keuangan RW')
@section('eyebrow', 'Bendahara')
@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="card p-4"><div class="kpi-label">Saldo kas</div><div class="kpi-value text-2xl">{{ \App\Support\Labels::money($balance) }}</div></article>
        <article class="card p-4"><div class="kpi-label">Masuk</div><div class="kpi-value text-2xl">{{ \App\Support\Labels::money($in) }}</div></article>
        <article class="card p-4"><div class="kpi-label">Keluar</div><div class="kpi-value text-2xl">{{ \App\Support\Labels::money($out) }}</div></article>
        <article class="card p-4"><div class="kpi-label">Pencairan menunggu</div><div class="kpi-value">{{ $waiting }}</div></article>
    </div>
@endsection
