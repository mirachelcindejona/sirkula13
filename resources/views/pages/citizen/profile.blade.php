@extends('layouts.app')
@section('title', 'Profil & ID warga')
@section('heading', 'Profil & ID warga')
@section('eyebrow', 'Identitas dan rumah tangga')
@section('subtitle', 'Periksa data akun, ID warga, status persetujuan, serta informasi yang terhubung.')
@section('content')
    @php($approved = ($profile?->status ?: $user->status) === 'active')
    @php($code = $approved ? ($profile?->resident_code ?: ($user->user_code ?: '—')) : null)
    @php($totals = $profile?->earnedPoints() ?? 0)
    @php($balance = $profile?->pointBalance() ?? 0)
    <div class="grid two">
        <div class="profile-card">
            <div class="eyebrow" style="color:#fff">Kartu identitas warga</div>
            <div class="profile-title">{{ $profile?->full_name ?: $user->name }}</div>
            <div class="profile-code">{{ $code ?: 'Belum terbit' }}</div>
            <p>RW 13 · Kelurahan Babakan Sari</p>
            <p>RT {{ $profile?->rt ?: '—' }} · Status {{ \App\Support\Labels::status($profile?->status ?: $user->status) }}</p>
            @if ($code)
                <button class="btn" type="button" onclick="navigator.clipboard.writeText(@js($code))"><x-icon name="clipboard" /> Salin ID warga</button>
                <p class="tiny" style="margin-top:12px">ID ini dipakai petugas saat mencatat setoran dan kehadiran. ID bukan kata sandi.</p>
            @else
                <p class="tiny" style="margin-top:12px">ID warga muncul setelah pengurus menyetujui akun. Sebelum itu, setoran dan penukaran poin belum bisa dipakai.</p>
            @endif
        </div>
        <div class="card">
            <div class="card-head">
                <h3 class="card-title">Status akun</h3>
                <x-badge :status="$profile?->status ?: $user->status" />
            </div>
            <div class="list-row"><div class="list-icon"><x-icon name="user" /></div><div class="grow"><h4>Nama sesuai pendaftaran</h4><p>{{ $profile?->full_name ?: $user->name }}</p></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="user" /></div><div class="grow"><h4>Email akun</h4><p>{{ $user->email ?: 'Belum diisi' }}</p><span class="tiny muted">{{ $user->email_verified_at ? 'Email terverifikasi' : 'Email belum diverifikasi' }}</span></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="receipt" /></div><div class="grow"><h4>Nomor kontak</h4><p>{{ $user->phone ?: ($profile?->phone ?: 'Belum diisi') }}</p></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="home" /></div><div class="grow"><h4>Alamat warga</h4><p>RT {{ $profile?->rt ?: '—' }} · No. rumah {{ $profile?->house_number ?: 'Belum diisi' }}<br>{{ $profile?->household_name ?: 'Nama kepala keluarga belum diisi' }}</p></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="shield" /></div><div class="grow"><h4>Role yang diberikan</h4><p>{{ $user->roles->pluck('name')->join(', ') ?: 'Belum ada role aktif' }}</p></div></div>
        </div>
    </div>
    <h2 class="section-title">Ringkasan kontribusi</h2>
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="award" /></div><div class="kpi-value">{{ number_format($totals, 0, ',', '.') }}</div><div class="kpi-label">Total EcoPoints</div><div class="kpi-note">Untuk penentuan rank</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="sparkles" /></div><div class="kpi-value">{{ number_format($balance, 0, ',', '.') }}</div><div class="kpi-label">Saldo poin</div><div class="kpi-note">Sebelum reservasi reward</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="award" /></div><div class="kpi-value">{{ $rank['name'] ?? '—' }}</div><div class="kpi-label">Rank saat ini</div><div class="kpi-note">Tidak turun saat poin ditukar</div></div>
    </div>
    <div class="callout warning gap-top"><x-icon name="shield" /><div><strong>Jaga data akun</strong>Gunakan akun sendiri. Jangan membagikan akun pengelola atau menganggap ID/QR sebagai kata sandi. Perubahan role hanya dilakukan Superadmin sesuai penugasan RW.</div></div>
@endsection
