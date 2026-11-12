@extends('layouts.app')
@section('title', 'Daftar kegiatan')
@section('heading', 'Kegiatan')
@section('eyebrow', 'Program')
@section('content')
    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($events as $event)
            <article class="card p-5">
                <div class="flex justify-between gap-3"><h2 class="text-lg font-extrabold">{{ $event->title }}</h2><x-badge :status="$event->status" /></div>
                <p class="mt-2 text-sm text-muted">{{ $event->event_date?->translatedFormat('d F Y') }} · {{ $event->location }}</p>
                <p class="mt-2 text-sm">Kuota {{ $event->quota ?: '—' }} · hadir {{ $event->attendance_points }} poin</p>
            </article>
        @empty
            <p class="text-sm text-muted">Belum ada kegiatan.</p>
        @endforelse
    </div>
    {{ $events->links() }}
@endsection
