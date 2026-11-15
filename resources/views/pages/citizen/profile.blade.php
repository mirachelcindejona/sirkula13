@extends('layouts.app')
@section('title', 'Profil saya')
@section('heading', 'Profil saya')
@section('eyebrow', 'Data dan ID warga')
@section('subtitle', 'Tunjukkan ID warga ke petugas setiap kali menyetor.')
@section('content')
    @php($approved = ($profile?->status ?: $user->status) === 'active')
    @php($code = $approved ? ($profile?->resident_code ?: ($user->user_code ?: '—')) : null)
    @php($totals = $profile?->earnedPoints() ?? 0)
    @php($balance = $profile?->pointBalance() ?? 0)
    <div class="grid two">
        <div class="profile-card">
            <div class="eyebrow" style="color:#fff">ID warga</div>
            <div class="profile-title">{{ $profile?->full_name ?: $user->name }}</div>
            <div class="profile-code">{{ $code ?: 'Belum terbit' }}</div>
            <p>RW 13 · Kelurahan Babakan Sari</p>
            <p>RT {{ $profile?->rt ?: '—' }} · Status {{ \App\Support\Labels::status($profile?->status ?: $user->status) }}</p>
            @if ($code)
                <button class="btn" type="button" onclick="navigator.clipboard.writeText(@js($code))"><x-icon name="clipboard" /> Salin ID warga</button>
                <p class="tiny" style="margin-top:12px">Sebutkan ID ini saat setor atau ikut kegiatan.</p>
            @else
                <p class="tiny" style="margin-top:12px">ID muncul setelah akun disetujui pengurus.</p>
            @endif
        </div>
        <div class="card">
            <div class="card-head">
                <h3 class="card-title">Status akun</h3>
                <x-badge :status="$profile?->status ?: $user->status" />
            </div>
            <div class="list-row"><div class="list-icon"><x-icon name="user" /></div><div class="grow"><h4>Nama</h4><p>{{ $profile?->full_name ?: $user->name }}</p></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="user" /></div><div class="grow"><h4>Email</h4><p>{{ $user->email ?: 'Belum diisi' }}</p></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="receipt" /></div><div class="grow"><h4>Nomor HP</h4><p>{{ $user->phone ?: ($profile?->phone ?: 'Belum diisi') }}</p></div></div>
            <div class="list-row"><div class="list-icon"><x-icon name="home" /></div><div class="grow"><h4>Alamat</h4><p>RT {{ $profile?->rt ?: '—' }} · No. rumah {{ $profile?->house_number ?: 'Belum diisi' }}<br>{{ $profile?->household_name ?: 'Nama kepala keluarga belum diisi' }}</p></div></div>
        </div>
    </div>
    <h2 class="section-title">Poin Anda</h2>
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="award" /></div><div class="kpi-value">{{ number_format($totals, 0, ',', '.') }}</div><div class="kpi-label">Total poin</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="sparkles" /></div><div class="kpi-value">{{ number_format($balance, 0, ',', '.') }}</div><div class="kpi-label">Sisa poin</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="award" /></div><div class="kpi-value">{{ $rank['name'] ?? '—' }}</div><div class="kpi-label">Rank</div><div class="kpi-note"><span>Tidak turun saat poin ditukar</span> <x-rank-guide :current="$rank['name'] ?? null" /></div></div>
    </div>
    <p class="small muted gap-top">Jangan bagikan kata sandi ke siapa pun, termasuk petugas.</p>
@endsection
