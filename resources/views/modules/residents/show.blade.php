@extends('layouts.app')
@section('content')
<main class="mx-auto max-w-5xl px-4 py-8 text-ink">
    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-primary-700">Administrasi warga</p>
    <h1 class="font-display text-4xl font-bold text-primary-900">Profil warga</h1>
    <p class="mt-2 mb-6 text-muted">Identitas internal, status persetujuan, dan keterkaitan dengan akun.</p>
    @include('modules.residents._nav')
    @include('modules.residents._flash')
    <section class="rounded-2xl border border-line bg-white p-6">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3"><div><div class="text-xs font-semibold uppercase text-muted">ID Warga</div><div class="font-display text-3xl font-bold text-primary-900">{{ $profile->resident_code ?? 'Belum diterbitkan' }}</div></div><span class="rounded-full bg-primary-100 px-4 py-2 text-sm font-bold text-primary-800">{{ ['active'=>'Aktif','pending'=>'Menunggu','needs_info'=>'Perlu data','rejected'=>'Ditolak'][$profile->status] ?? $profile->status }}</span></div>
        <dl class="grid gap-4 md:grid-cols-2">
            @foreach(['Nama lengkap'=>$profile->full_name, 'Nomor HP'=>$profile->phone ?? '—', 'RT'=>$profile->rt, 'Nomor rumah'=>$profile->house_number ?? '—', 'Kepala keluarga'=>$profile->household_name ?? '—', 'Alias'=>$profile->display_name ?? '—', 'Leaderboard'=>$profile->leaderboard_visible ? 'Ditampilkan (alias)' : 'Disembunyikan', 'Akun online'=>$profile->user ? 'Terhubung · '.$profile->user->status : 'Belum terhubung', 'Dibuat oleh'=>$profile->creator?->name ?? 'Data awal / pendaftaran online', 'Disetujui oleh'=>$profile->approver?->name ?? '—'] as $label => $value)
                <div class="border-b border-line pb-3"><dt class="text-xs font-semibold text-muted">{{ $label }}</dt><dd class="mt-1 font-semibold">{{ $value }}</dd></div>
            @endforeach
        </dl>
        @if($profile->review_note)<div class="mt-5 rounded-xl bg-amber-50 p-4"><strong>Catatan pemeriksaan:</strong> {{ $profile->review_note }}</div>@endif
        <div class="mt-6"><a href="{{ route('residents.edit', $profile) }}" class="inline-block rounded-xl bg-primary-500 px-6 py-3 font-bold text-white">Edit profil</a></div>
    </section>
</main>
@endsection
