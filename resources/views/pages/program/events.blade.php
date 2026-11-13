@extends('layouts.app')
@section('title', 'Kegiatan dan kehadiran')
@section('heading', 'Kegiatan & kehadiran')
@section('eyebrow', 'Pengelola kegiatan')
@section('subtitle', 'Kegiatan yang belum berlangsung masih bisa diubah. Yang sudah lewat tetap bisa dilihat dan dicatat kehadirannya.')
@section('actions')
    <a class="btn primary" href="{{ route('program.events.create') }}">Tambah kegiatan</a>
@endsection
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari nama, lokasi, atau kategori" :filters="[['name' => 'status', 'label' => 'Semua status', 'options' => ['published' => 'Tayang', 'draft' => 'Draft']]]" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kegiatan</th><th>Jadwal</th><th>Lokasi</th><th>Poin</th><th>Hadir</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td><strong>{{ $event->title }}</strong><br><span class="small muted">{{ $event->category ?: 'Umum' }}</span></td>
                            <td>{{ $event->event_date?->locale('id')->translatedFormat('d M Y') }}<br><span class="small muted">{{ $event->event_time ?: 'Waktu menyusul' }}</span></td>
                            <td>{{ $event->location }}</td>
                            <td>{{ $event->attendance_points }}</td>
                            <td>{{ $event->attendances_count }}</td>
                            <td><x-badge :status="$event->status" /></td>
                            <td><a class="btn small-btn secondary" href="{{ route('program.events.show', $event) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada kegiatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $events->links() }}
    </div>
@endsection
