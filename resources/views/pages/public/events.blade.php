@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Kegiatan warga')
@section('heading', 'Kegiatan warga')
@section('eyebrow', 'Ikut, kumpul, dapat poin')
@section('subtitle', 'Daftar kegiatan yang Anda minati. Poin masuk setelah Anda hadir.')
@section('content')
    @php
        $user = auth()->user();
        $profile = $user?->residentProfile;
        $citizen = $user?->hasRole('warga') && $profile !== null;
        $approved = $profile?->status === 'active';
    @endphp
    @guest
        <div class="public-subpage-head">
            <div class="eyebrow">SIRKULA-CERDAS · RW 13</div>
            <h1>Kegiatan warga</h1>
            <p>Daftar kegiatan yang Anda minati. Poin masuk setelah Anda hadir.</p>
        </div>
    @endguest

    <div class="grid two">
        @forelse ($events as $event)
            @php($registration = $profile ? $event->registrations->firstWhere('resident_profile_id', $profile->id) : null)
            <article class="card event-card">
                <div class="event-poster"><img src="{{ $event->posterUrl() }}" alt="Poster {{ $event->title }}"></div>
                <div class="card-head">
                    <span class="badge success">{{ $event->event_date?->locale('id')->translatedFormat('d M Y') }}</span>
                    <span class="point-pill">+{{ $event->attendance_points }} poin hadir</span>
                </div>
                <h3 class="card-title">{{ $event->title }}</h3>
                <p class="card-subtitle">{{ $event->event_time }} · {{ $event->location }}</p>
                <p class="small">{{ $event->description }}</p>
                <div class="divider"></div>
                <div class="row between">
                    <span class="small muted">{{ $event->registrations->count() }}{{ $event->quota ? ' dari '.$event->quota : '' }} peserta</span>
                    @if ($registration)
                        <x-badge :status="$registration->status" />
                    @endif
                </div>
                <div class="gap-top">
                    @guest
                        <a class="btn outline block" href="{{ route('login') }}">Masuk untuk mendaftar</a>
                    @else
                        @if (! $citizen)
                            <button class="btn outline block" type="button" disabled>Masuk sebagai warga untuk mendaftar</button>
                        @elseif (! $approved)
                            <button class="btn outline block" type="button" disabled>Bisa daftar setelah akun disetujui</button>
                        @elseif ($registration && $registration->status === 'attended')
                            <p class="small">Terima kasih sudah hadir. Poinnya sudah masuk.</p>
                        @elseif ($registration)
                            <form method="POST" action="{{ route('public.events.cancel', $event) }}" data-confirm="Batalkan pendaftaran {{ $event->title }}?">
                                @csrf
                                <button class="btn danger block" type="submit">Batalkan pendaftaran</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('public.events.register', $event) }}" data-confirm="Daftar {{ $event->title }}? Poin masuk setelah Anda hadir.">
                                @csrf
                                <button class="btn primary block" type="submit">Daftar</button>
                            </form>
                        @endif
                    @endguest
                </div>
            </article>
        @empty
            <div class="card empty-state"><h3>Belum ada kegiatan</h3><p>Nanti kalau ada jadwal baru, langsung muncul di sini.</p></div>
        @endforelse
    </div>

    @if ($citizen)
        <h2 class="section-title">Pendaftaran saya</h2>
        <div class="card">
            @if ($mine->isEmpty())
                <p class="small muted">Anda belum daftar kegiatan apa pun.</p>
            @else
                @foreach ($mine as $registration)
                    <div class="list-row">
                        <div class="list-icon"><x-icon name="calendar" /></div>
                        <div class="grow">
                            <h4>{{ $registration->event?->title }}</h4>
                            <p>{{ $registration->event?->event_date?->locale('id')->translatedFormat('d M Y') }} · {{ $registration->event?->location }}</p>
                        </div>
                        <x-badge :status="$registration->status" />
                    </div>
                @endforeach
            @endif
        </div>
    @endif
@endsection
