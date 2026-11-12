@extends('layouts.app')
@section('content')
<main class="mx-auto max-w-6xl px-4 py-8 text-ink">
    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-primary-700">Superadmin · Administrasi RW 13</p>
    <h1 class="font-display text-4xl font-bold text-primary-900">Persetujuan akun warga</h1>
    <p class="mt-2 mb-6 text-muted">Tinjau pendaftaran, periksa kemungkinan duplikasi, lalu setujui, minta perbaikan, atau tolak disertai alasan.</p>
    @include('modules.residents._nav')
    @include('modules.residents._flash')
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm"><strong>Satu warga, satu profil utama.</strong> Jika pendaftar sudah punya ID manual, tautkan ke profil yang sama. Menautkan tidak otomatis mengaktifkan akun; persetujuan tetap diperlukan.</div>
    <h2 class="mb-4 text-2xl font-bold text-primary-900">Antrean pendaftaran ({{ $pending->count() }})</h2>
    <div class="space-y-5">
    @forelse($pending as $profile)
        <article class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="text-xl font-bold">{{ $profile->full_name }}</h3><p class="text-sm text-muted">{{ $profile->phone }} · RT {{ $profile->rt }} / No. {{ $profile->house_number ?? 'Belum diisi' }}</p><p class="text-sm text-muted">Kepala keluarga: {{ $profile->household_name ?? '—' }} · {{ $profile->created_at->format('d/m/Y H:i') }}</p><p class="text-sm text-muted">ID: {{ $profile->resident_code ?? 'Belum diterbitkan' }}</p></div><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">{{ $profile->user->status === 'needs_info' ? 'Menunggu perbaikan' : 'Menunggu pemeriksaan' }}</span></div>
            @if($profile->review_note)<p class="mt-3 rounded-xl bg-amber-50 p-3 text-sm">Catatan sebelumnya: {{ $profile->review_note }}</p>@endif
            <form action="{{ route('residents.approvals.review',$profile) }}" method="POST" class="mt-4 rounded-xl border border-primary-100 p-4">@csrf
                <label for="reason_{{ $profile->id }}" class="text-sm font-semibold">Alasan / catatan keputusan (wajib untuk penolakan dan perbaikan)</label>
                <textarea name="reason" id="reason_{{ $profile->id }}" rows="2" maxlength="2000" class="mt-1 w-full rounded-xl border border-primary-200 p-3" placeholder="Tulis data yang kurang, alasan penolakan, atau dasar persetujuan"></textarea>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button name="decision" value="approved" class="rounded-xl bg-primary-500 px-4 py-2 font-bold text-white">Setujui akun</button>
                    <button name="decision" value="needs_info" class="rounded-xl bg-amber-100 px-4 py-2 font-bold text-amber-900">Minta data</button>
                    <button name="decision" value="rejected" class="rounded-xl bg-red-100 px-4 py-2 font-bold text-red-800">Tolak</button>
                </div>
            </form>
            @if($profile->resident_code === null)
                <form action="{{ route('residents.approvals.link',$profile) }}" method="POST" class="mt-4 rounded-xl bg-primary-50 p-4">@csrf
                    <h4 class="mb-2 font-bold text-primary-900">Tautkan profil manual lama</h4>
                    <div class="grid gap-3 md:grid-cols-2"><div><label for="target_{{ $profile->id }}" class="text-sm font-semibold">Profil tujuan *</label><select required name="target_profile_id" id="target_{{ $profile->id }}" class="mt-1 w-full rounded-xl border border-primary-200 bg-white p-3"><option value="">Pilih profil yang sudah diverifikasi</option>@foreach($manualProfiles as $target)<option value="{{ $target->id }}">{{ $target->resident_code }} · {{ $target->full_name }} · RT {{ $target->rt }} / No. {{ $target->house_number ?? '—' }}</option>@endforeach</select></div><div><label for="link_reason_{{ $profile->id }}" class="text-sm font-semibold">Dasar pencocokan *</label><input id="link_reason_{{ $profile->id }}" name="reason" required minlength="8" maxlength="2000" class="mt-1 w-full rounded-xl border border-primary-200 bg-white p-3" placeholder="Contoh: verifikasi ID dan alamat oleh pengurus"></div></div>
                    <button type="submit" class="mt-3 rounded-xl border border-primary-500 px-4 py-2 font-bold text-primary-700">Tautkan (belum aktif)</button>
                </form>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-line bg-white p-8 text-center text-muted">Tidak ada pendaftaran yang menunggu pemeriksaan.</div>
    @endforelse
    </div>
</main>
@endsection
