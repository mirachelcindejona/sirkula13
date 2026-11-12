@extends('layouts.app')
@section('title', 'Data warga')
@section('heading', $canRegister ? 'Daftarkan warga tanpa HP' : 'Data warga')
@section('eyebrow', $canRegister ? 'Tetap terlayani tanpa akun online' : 'Direktori internal pengurus')
@section('subtitle', $canRegister ? 'Cari data lebih dulu, buat ID warga unik jika belum ada, lalu gunakan ID tersebut untuk pencatatan.' : 'Data ini hanya untuk petugas berwenang. Nomor rumah dan kontak tidak ditampilkan pada halaman publik.')
@section('content')
    @if ($canRegister)
        <div class="callout"><x-icon name="users" /><div><strong>Direktori internal pengurus</strong>Cari nama atau nomor kontak sebelum membuat ID baru agar tidak terjadi data ganda.</div></div>
        <div class="grid two gap-top">
            <div class="card">
                <h3 class="card-title">Buat profil warga tanpa HP</h3>
                <p class="card-subtitle">Untuk warga yang belum memakai akun online. ID dipakai untuk pencatatan, bukan untuk masuk.</p>
                <form method="POST" action="{{ route('bank.residents.store') }}" class="gap-top" data-confirm="Buat ID warga baru? Pastikan data yang sama belum ada.">
                    @csrf
                    <div class="form-grid">
                        <div class="field full">
                            <label for="manual-name">Nama warga <span class="required">*</span></label>
                            <input id="manual-name" name="name" required maxlength="150" value="{{ old('name') }}">
                        </div>
                        <div class="field">
                            <label for="manual-phone">Nomor kontak (opsional)</label>
                            <input id="manual-phone" name="phone" inputmode="tel" value="{{ old('phone') }}" placeholder="08xx">
                        </div>
                        <div class="field">
                            <label for="manual-rt">RT <span class="required">*</span></label>
                            <select id="manual-rt" name="rt" required>
                                <option value="">Pilih RT</option>
                                @foreach (range(1, 9) as $rt)
                                    @php($value = str_pad((string) $rt, 2, '0', STR_PAD_LEFT))
                                    <option value="{{ $value }}" @selected(old('rt') === $value)>RT {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="manual-house">Nomor rumah <span class="required">*</span></label>
                            <input id="manual-house" name="house_number" required maxlength="30" value="{{ old('house_number') }}" placeholder="Contoh: 12A">
                        </div>
                        <div class="field">
                            <label for="manual-household">Nama kepala keluarga (opsional)</label>
                            <input id="manual-household" name="household" maxlength="150" value="{{ old('household') }}">
                        </div>
                        <div class="field full">
                            <div class="checkline">
                                <input id="manual-dedupe" name="checked_duplicate" type="checkbox" value="1" required @checked(old('checked_duplicate'))>
                                <label for="manual-dedupe">Saya sudah mencari nama dan nomor kontak agar tidak membuat profil ganda.</label>
                            </div>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="callout warning gap-top">{{ $errors->first() }}</div>
                    @endif
                    <div class="form-actions">
                        <a class="btn outline" href="{{ route('admin.dashboard') }}">Batal</a>
                        <button class="btn primary" type="submit">Buat ID warga</button>
                    </div>
                </form>
            </div>
            <div class="card">
                <h3 class="card-title">Cari profil warga</h3>
                <form method="GET" action="{{ route('bank.residents') }}">
                    <div class="field">
                        <label for="resident-search">Pencarian warga</label>
                        <input id="resident-search" name="q" value="{{ $search }}" placeholder="Ketik nama, ID, RT, atau nomor kontak">
                    </div>
                    <div class="form-actions">
                        <button class="btn secondary" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <form method="GET" action="{{ route('bank.residents') }}" class="card">
            <div class="field">
                <label for="resident-search">Cari nama, ID warga, nomor HP, RT, atau nomor rumah</label>
                <input id="resident-search" name="q" value="{{ $search }}" placeholder="Contoh: Sari, RT 03, 12A">
            </div>
            <div class="form-actions"><button class="btn secondary" type="submit">Cari</button></div>
        </form>
    @endif

    <h2 class="section-title">Warga terdaftar</h2>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama warga</th>
                        <th>RT</th>
                        <th>No. rumah</th>
                        <th>Kontak</th>
                        <th>Status akun</th>
                        @if ($canRegister)<th>Tindakan</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($residents as $resident)
                        <tr>
                            <td><strong>{{ $resident->resident_code ?: '—' }}</strong></td>
                            <td>{{ $resident->full_name }}</td>
                            <td>{{ $resident->rt ?: '—' }}</td>
                            <td>{{ $resident->house_number ?: 'Belum diisi' }}</td>
                            <td>{{ $resident->phone ?: 'HP belum diisi' }}</td>
                            <td>
                                <x-badge :status="$resident->status" />
                                <div class="tiny muted">{{ $resident->user?->login_enabled ? 'Akun online' : 'Profil dibantu petugas' }}</div>
                            </td>
                            @if ($canRegister)
                                <td>
                                    @if ($resident->status === 'active')
                                        <form method="POST" action="{{ route('admin.residents.status', $resident) }}" data-confirm="Nonaktifkan akun {{ $resident->full_name }}? Warga tidak bisa masuk sampai diaktifkan kembali.">
                                            @csrf
                                            <input type="hidden" name="status" value="inactive">
                                            <button class="btn small-btn danger" type="submit">Nonaktifkan</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.residents.status', $resident) }}" data-confirm="Aktifkan kembali akun {{ $resident->full_name }}?">
                                            @csrf
                                            <input type="hidden" name="status" value="active">
                                            <button class="btn small-btn secondary" type="submit">Aktifkan</button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canRegister ? 7 : 6 }}">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $residents->links() }}
    </div>
@endsection
