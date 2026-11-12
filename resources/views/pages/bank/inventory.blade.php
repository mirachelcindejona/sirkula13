@extends('layouts.app')
@section('title', 'Stok')
@section('heading', 'Stok material')
@section('eyebrow', 'Operator')
@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($materials as $material)
            <article class="card p-5">
                <div class="text-2xl">{{ $material->icon ?: '♻️' }}</div>
                <h2 class="mt-2 text-lg font-extrabold">{{ $material->name }}</h2>
                <div class="kpi-value mt-2 text-2xl">{{ \App\Support\Labels::kg($material->stock_kg) }}</div>
            </article>
        @endforeach
    </div>
@endsection
