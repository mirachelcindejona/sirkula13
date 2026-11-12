@extends('layouts.app')
@section('title', 'Role & hak akses')
@section('heading', 'Role & hak akses')
@section('eyebrow', 'Akses sesuai tugas, bukan pilihan sendiri')
@section('subtitle', 'Lima role resmi digunakan. Setiap pemberian akses harus memiliki alasan.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="callout warning"><x-icon name="shield" /><div><strong>Lima role resmi saja</strong>Warga, Operator Bank Sampah, Pengelola Kegiatan, Bendahara, dan Superadmin. Role Superadmin tidak dapat ditambahkan dari menu ini.</div></div>
    <div class="grid two gap-top">
        <div class="card">
            <h3 class="card-title">Tetapkan role operasional</h3>
            <p class="card-subtitle">Pilih orang yang penugasannya sudah disetujui RW.</p>
            <form method="POST" action="{{ route('admin.roles.store') }}" class="gap-top" data-confirm="Terapkan role ini pada akun yang dipilih?">
                @csrf
                <div class="form-grid">
                    <div class="field full">
                        <label for="role-user">Akun pengguna <span class="required">*</span></label>
                        <select id="role-user" name="user_id" required>
                            <option value="">Pilih akun</option>
                            @foreach ($staff as $account)
                                <option value="{{ $account->id }}" @selected(old('user_id') == $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field full">
                        <label for="role-name">Role yang akan ditambahkan <span class="required">*</span></label>
                        <select id="role-name" name="role" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->slug }}" @selected(old('role') === $role->slug)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field full">
                        <label for="role-reason">Alasan / dasar penugasan <span class="required">*</span></label>
                        <textarea id="role-reason" name="reason" required maxlength="500">{{ old('reason') }}</textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn outline" type="reset">Bersihkan</button>
                    <button class="btn primary" type="submit">Terapkan role</button>
                </div>
            </form>
        </div>
        <div class="card">
            <h3 class="card-title">Prinsip akses</h3>
            <div class="flow-step"><div class="step-number">1</div><div><h4>Akses minimum</h4><p>Pengguna hanya mendapat menu sesuai tanggung jawab operasional.</p></div></div>
            <div class="flow-step"><div class="step-number">2</div><div><h4>Pemisahan tugas</h4><p>Pembuat transaksi tidak mengesahkan transaksi yang sama.</p></div></div>
            <div class="flow-step"><div class="step-number">3</div><div><h4>Cabut akses saat tugas berakhir</h4><p>Nonaktifkan akun dari Data warga bila warga atau petugas sudah tidak bertugas.</p></div></div>
        </div>
    </div>
    <h2 class="section-title">Daftar role yang diberikan</h2>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Role aktif</th><th>Akses</th></tr></thead>
                <tbody>
                    @foreach ($accounts as $account)
                        <tr>
                            <td><strong>{{ $account->name }}</strong></td>
                            <td>
                                @forelse ($account->roles as $role)
                                    <span class="badge info">{{ $role->name }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td>{{ $account->login_enabled ? 'Akun online' : 'Tidak bisa masuk' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $accounts->links() }}
    </div>
@endsection
