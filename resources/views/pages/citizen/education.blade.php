@extends('layouts.app')
@section('title', 'Edukasi')
@section('heading', 'Materi edukasi')
@section('eyebrow', 'Warga')
@section('content')
    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($materials as $material)
            <article class="card p-5">
                <div class="text-xs font-extrabold tracking-wide text-primary-700 uppercase">{{ $material->category ?: 'Edukasi' }}</div>
                <h2 class="mt-1 text-xl font-extrabold">{{ $material->title }}</h2>
                <p class="mt-2 line-clamp-4 text-sm text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($material->content), 180) }}</p>
            </article>
        @empty
            <p class="text-sm text-muted">Belum ada materi tayang.</p>
        @endforelse
    </div>
@endsection
