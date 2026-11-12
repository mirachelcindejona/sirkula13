@extends('layouts.app')
@section('content')
<main class="mx-auto max-w-5xl px-4 py-8 text-ink">
    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-primary-700">Bank sampah · Layanan warga tanpa akun</p>
    <h1 class="font-display text-4xl font-bold text-primary-900">Buat profil warga manual</h1>
    <p class="mt-2 mb-6 text-muted">Warga tetap dapat memiliki ID untuk setoran dan kegiatan walaupun tidak memiliki HP atau kata sandi.</p>
    @include('modules.residents._nav')
    @include('modules.residents._flash')
    <div class="grid gap-5 lg:grid-cols-3">
        <section class="rounded-2xl border border-line bg-white p-6 lg:col-span-2">
            <h2 class="mb-4 text-xl font-bold">Data warga</h2>
            <form method="POST" action="{{ route('residents.store') }}">@csrf
                @include('modules.residents._form')
                <button type="submit" class="mt-6 rounded-xl bg-primary-500 px-6 py-3 font-bold text-white hover:bg-primary-700">Buat ID warga</button>
            </form>
        </section>
        <aside class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm">
            <h2 class="mb-3 text-lg font-bold text-amber-900">Cegah profil ganda</h2>
            <p class="mb-3">Cari nama, nomor kontak, RT, atau nomor rumah terlebih dahulu.</p>
            <a href="{{ route('residents.index') }}" class="font-bold text-primary-700 underline">Buka pencarian warga</a>
            <p class="mt-4">ID warga digunakan untuk transaksi manual, <strong>bukan sebagai kata sandi</strong>. Akun online dapat ditautkan nanti oleh Superadmin.</p>
        </aside>
    </div>
</main>
@endsection
