@extends('layouts.app')
@section('title', 'Persetujuan akun warga')
@section('heading', 'Persetujuan akun warga')
@section('eyebrow', 'Satu warga, satu profil utama')
@section('subtitle', 'Periksa apakah data ini milik warga yang sama, lalu setujui atau tolak.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="callout"><x-icon name="user" /><div><strong>Periksa potensi duplikasi sebelum menyetujui</strong>Jika warga sudah punya ID, jangan membuat saldo kedua. Akun hanya disetujui oleh pengurus, bukan oleh pemilik akun sendiri.</div></div>
    <div class="card gap-top">
        <x-table-toolbar placeholder="Cari nama, email, atau nomor HP" />
        @forelse ($users as $user)
            <div class="list-row">
                <div class="list-icon"><x-icon name="user" /></div>
                <div class="grow">
                    <h4>{{ $user->name }}</h4>
                    <p>Email: {{ $user->email }}<br>HP: {{ $user->phone ?: 'Kontak belum diisi' }} · RT {{ $user->residentProfile?->rt ?: '—' }} · No. rumah {{ $user->residentProfile?->house_number ?: 'Belum diisi' }}<br>Rumah tangga: {{ $user->residentProfile?->household_name ?: 'Belum diisi' }}</p>
                    <x-badge :status="$user->status" />
                </div>
                <div class="stack">
                    <form method="POST" action="{{ route('admin.approvals.store', $user) }}" data-confirm="Setujui pendaftaran {{ $user->name }}?">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button class="btn small-btn primary" type="submit">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('admin.approvals.store', $user) }}" data-confirm="Tolak pendaftaran {{ $user->name }}? Akun tidak bisa masuk.">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <button class="btn small-btn danger" type="submit">Tolak</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state"><h3>Tidak ada pendaftaran menunggu</h3><p>Pendaftaran baru akan muncul di sini setelah warga mengirim formulir.</p></div>
        @endforelse
        {{ $users->links() }}
    </div>
    <h2 class="section-title">Akun yang sudah diproses</h2>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama / ID</th><th>Email / Kontak</th><th>Status</th><th>Role</th><th>Akses</th></tr></thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td><strong>{{ $account->name }}</strong><br><span class="small muted">{{ $account->residentProfile?->resident_code ?: $account->user_code ?: '—' }}</span></td>
                            <td>{{ $account->email }}<br><span class="small muted">{{ $account->phone ?: 'HP belum diisi' }}</span></td>
                            <td><x-badge :status="$account->status" /></td>
                            <td>{{ $account->roles->pluck('name')->join(', ') ?: '—' }}</td>
                            <td>{{ $account->login_enabled ? 'Akun online' : 'Profil manual saja' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada akun lain.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $accounts->links() }}
    </div>
@endsection
