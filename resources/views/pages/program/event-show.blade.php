@extends('layouts.app')
@section('title', $event->title)
@section('heading', $event->title)
@section('eyebrow', 'Detail kegiatan')
@section('subtitle', $event->event_date?->locale('id')->translatedFormat('d F Y').' · '.($event->event_time ?: 'Waktu menyusul').' · '.$event->location)
@section('actions')
    <a class="btn outline" href="{{ route('program.events') }}">Kembali</a>
@endsection
@section('content')
    <div class="grid two">
        <div class="card">
            <div class="row between"><h3 class="card-title">Informasi</h3><x-badge :status="$event->status" /></div>
            <div class="event-poster gap-top"><img src="{{ $event->posterUrl() }}" alt="Poster {{ $event->title }}"></div>
            <p class="gap-top">{{ $event->description ?: 'Belum ada keterangan.' }}</p>
            <p class="small muted">Kuota {{ $event->quota ?: 'tidak dibatasi' }} · {{ $event->attendance_points }} poin untuk yang hadir.</p>
        </div>
        @if (! $event->hasStarted())
            <div class="card">
                <h3 class="card-title">Ubah kegiatan</h3>
                <p class="card-subtitle">Bisa diubah karena kegiatan belum berlangsung.</p>
                <form method="POST" action="{{ route('program.events.update', $event) }}" class="gap-top" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('pages.program._event-fields', ['event' => $event])
                    <div class="form-actions">
                        <button class="btn outline" name="publish" value="draft" type="submit">Simpan draft</button>
                        <button class="btn primary" name="publish" value="published" type="submit">Tayangkan</button>
                    </div>
                </form>
            </div>
        @else
            <div class="callout"><x-icon name="calendar" /><div><strong>Kegiatan sudah berlangsung</strong>Data jadwal dikunci. Kehadiran tetap bisa dilengkapi.</div></div>
        @endif
    </div>
    <h2 class="section-title">Checklist kehadiran</h2>
    <div class="card">
        <form method="POST" action="{{ route('program.events.attendance', $event) }}">
            @csrf
            <div class="table-search">
                <label class="sr-only" for="att-search">Cari warga</label>
                <input id="att-search" type="search" placeholder="Cari nama atau ID warga">
            </div>
            <div class="checkline gap-top">
                <input type="checkbox" id="att-all">
                <label for="att-all">Pilih semua yang sedang tampil</label>
            </div>
            <div class="stack gap-top" id="att-list">
                @foreach ($residents as $resident)
                    @php($done = $attended->contains($resident->id))
                    <label class="checkline att-row" data-search="{{ strtolower($resident->full_name.' '.$resident->resident_code) }}">
                        <input type="checkbox" name="residents[]" value="{{ $resident->id }}" @checked($done) @disabled($done)>
                        <span><strong>{{ $resident->full_name }}</strong> · {{ $resident->resident_code }} · RT {{ $resident->rt ?: '—' }}@if($done) · sudah hadir @endif</span>
                    </label>
                @endforeach
            </div>
            <div class="form-actions">
                <button class="btn primary" type="submit">Simpan kehadiran</button>
            </div>
        </form>
    </div>
    <script>
        (() => {
            const search = document.getElementById('att-search');
            const rows = [...document.querySelectorAll('.att-row')];
            const all = document.getElementById('att-all');
            const visible = () => rows.filter((row) => !row.hidden);
            search.addEventListener('input', () => {
                const text = search.value.toLowerCase();
                rows.forEach((row) => { row.hidden = text !== '' && !row.dataset.search.includes(text); });
            });
            all.addEventListener('change', () => {
                visible().forEach((row) => {
                    const box = row.querySelector('input');
                    if (!box.disabled) box.checked = all.checked;
                });
            });
        })();
    </script>
@endsection
