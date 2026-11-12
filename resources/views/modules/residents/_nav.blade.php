<nav aria-label="Navigasi administrasi warga" class="mb-6 flex flex-wrap gap-2 text-sm">
    <a href="{{ route('residents.index') }}" class="rounded-xl border border-primary-200 bg-white px-4 py-2 font-semibold text-primary-800 hover:bg-primary-100">Daftar warga</a>
    <a href="{{ route('residents.create') }}" class="rounded-xl border border-primary-200 bg-white px-4 py-2 font-semibold text-primary-800 hover:bg-primary-100">Buat ID manual</a>
    @if (\App\Services\Residents\ResidentAccess::hasRole(auth()->user(), 'superadmin'))
        <a href="{{ route('residents.approvals.index') }}" class="rounded-xl border border-primary-200 bg-white px-4 py-2 font-semibold text-primary-800 hover:bg-primary-100">Persetujuan akun</a>
        <a href="{{ route('readiness.index') }}" class="rounded-xl border border-primary-200 bg-white px-4 py-2 font-semibold text-primary-800 hover:bg-primary-100">Kesiapan operasional</a>
    @endif
</nav>
