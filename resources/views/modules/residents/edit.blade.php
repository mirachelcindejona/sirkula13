@extends('layouts.app')
@section('content')
<main class="mx-auto max-w-4xl px-4 py-8 text-ink">
    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-primary-700">Administrasi warga</p>
    <h1 class="font-display text-4xl font-bold text-primary-900">Ubah profil {{ $profile->resident_code }}</h1>
    <p class="mt-2 mb-6 text-muted">Edit data administrasi tanpa mengganti ID warga atau saldo riwayat.</p>
    @include('modules.residents._nav')
    @include('modules.residents._flash')
    <section class="rounded-2xl border border-line bg-white p-6"><form method="POST" action="{{ route('residents.update', $profile) }}">@csrf @method('PUT')
        @include('modules.residents._form')
        <div class="mt-6 flex flex-wrap gap-3"><button class="rounded-xl bg-primary-500 px-6 py-3 font-bold text-white">Simpan perubahan</button><a class="rounded-xl border border-primary-200 px-6 py-3 text-primary-800" href="{{ route('residents.show',$profile) }}">Batal</a></div>
    </form></section>
</main>
@endsection
