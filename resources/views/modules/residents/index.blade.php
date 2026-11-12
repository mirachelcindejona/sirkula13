@extends('layouts.app')
@section('content')
<main class="mx-auto max-w-6xl px-4 py-8 text-ink">
    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-primary-700">Bank sampah · Administrasi RW 13</p>
    <h1 class="font-display text-4xl font-bold text-primary-900">Daftar warga</h1>
    <p class="mt-2 mb-6 text-muted">Direktori internal pengurus. Nomor HP, RT, dan nomor rumah tidak boleh ditampilkan pada halaman publik.</p>
    @include('modules.residents._nav')
    @include('modules.residents._flash')
    <form action="{{ route('residents.index') }}" method="GET" class="mb-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-5">
        <div class="md:col-span-2"><label for="q" class="text-sm font-semibold">Cari nama, ID, HP, atau nomor rumah</label><input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: Sari, W-001, 12A" class="mt-1 w-full rounded-xl border border-primary-200 p-3"></div>
        <div><label for="rt" class="text-sm font-semibold">Filter RT</label><select id="rt" name="rt" class="mt-1 w-full rounded-xl border border-primary-200 p-3"><option value="">Semua RT</option>@for ($n=1;$n<=9;$n++) @php $rt=str_pad((string)$n,2,'0',STR_PAD_LEFT); @endphp <option value="{{ $rt }}" @selected(($filters['rt']??'')===$rt)>RT {{ $rt }}</option> @endfor</select></div>
        <div><label for="status" class="text-sm font-semibold">Status profil</label><select id="status" name="status" class="mt-1 w-full rounded-xl border border-primary-200 p-3"><option value="">Semua</option>@foreach(['active'=>'Aktif','pending'=>'Menunggu','needs_info'=>'Perlu data','rejected'=>'Ditolak'] as $status=>$label)<option value="{{ $status }}" @selected(($filters['status']??'')===$status)>{{ $label }}</option>@endforeach</select></div>
        <div><label for="access" class="text-sm font-semibold">Jenis akses</label><select id="access" name="access" class="mt-1 w-full rounded-xl border border-primary-200 p-3"><option value="">Semua</option><option value="manual" @selected(($filters['access']??'')==='manual')>Profil manual</option><option value="online" @selected(($filters['access']??'')==='online')>Akun terhubung</option></select></div>
        <div class="flex flex-wrap gap-2 md:col-span-5"><button class="rounded-xl bg-primary-500 px-5 py-3 font-bold text-white hover:bg-primary-700">Cari / Filter</button><a href="{{ route('residents.index') }}" class="rounded-xl border border-primary-200 px-4 py-3 font-semibold text-primary-800">Reset</a></div>
    </form>
    <div class="overflow-x-auto rounded-2xl border border-line bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-primary-100 text-primary-900"><tr><th class="p-4">ID</th><th class="p-4">Nama warga</th><th class="p-4">RT</th><th class="p-4">No. rumah</th><th class="p-4">Kontak</th><th class="p-4">Status / akses</th><th class="p-4">Detail</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse($profiles as $profile)
                    <tr><td class="p-4 font-bold">{{ $profile->resident_code ?? 'Belum terbit' }}</td><td class="p-4">{{ $profile->full_name }}</td><td class="p-4">{{ $profile->rt }}</td><td class="p-4">{{ $profile->house_number ?? '—' }}</td><td class="p-4">{{ $profile->phone ?? '—' }}</td><td class="p-4"><span class="font-semibold">{{ ['active'=>'Aktif','pending'=>'Menunggu','needs_info'=>'Perlu data','rejected'=>'Ditolak'][$profile->status] ?? $profile->status }}</span><div class="text-xs text-muted">{{ $profile->user_id ? 'Akun: '.(['active'=>'Aktif','pending'=>'Menunggu','needs_info'=>'Perlu data','rejected'=>'Ditolak'][$profile->user?->status] ?? $profile->user?->status) : 'Profil manual' }}</div></td><td class="p-4"><a class="font-bold text-primary-700 underline" href="{{ route('residents.show',$profile) }}">Lihat</a></td></tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-muted">Data warga tidak ditemukan. Periksa filter atau buat profil baru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $profiles->links() }}</div>
</main>
@endsection
