@extends('layouts.public')
@section('title', 'Daftar — SIRKULA RW13')
@section('content')
    <section class="public-section">
        <div class="public-section-head">
            <div class="eyebrow">Pendaftaran warga RW 13</div>
            <h1>Daftar akun SIRKULA RW13</h1>
            <p>Isi data dengan benar. Pengurus akan memeriksa pendaftaran sebelum akun dapat digunakan.</p>
        </div>
        <div class="register-stack">
            <div class="card">
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="field full">
                            <label for="reg-name">Nama lengkap <span class="required">*</span></label>
                            <input id="reg-name" name="name" required maxlength="150" value="{{ old('name') }}" placeholder="Nama sesuai identitas atau yang dikenal warga">
                        </div>
                        <div class="field full">
                            <label for="reg-email">Alamat email aktif <span class="required">*</span></label>
                            <input id="reg-email" name="email" type="email" required maxlength="255" value="{{ old('email') }}" placeholder="contoh: nama@gmail.com">
                            <div class="field-hint">Dipakai untuk masuk dan mengatur ulang kata sandi.</div>
                        </div>
                        <div class="field">
                            <label for="reg-phone">Nomor HP aktif <span class="required">*</span></label>
                            <input id="reg-phone" name="phone" type="tel" required value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="field">
                            <label for="reg-rt">RT <span class="required">*</span></label>
                            <select id="reg-rt" name="rt" required>
                                <option value="">Pilih RT</option>
                                @foreach (range(1, 9) as $rt)
                                    <option value="{{ str_pad((string) $rt, 2, '0', STR_PAD_LEFT) }}" @selected(old('rt') === str_pad((string) $rt, 2, '0', STR_PAD_LEFT))>RT {{ str_pad((string) $rt, 2, '0', STR_PAD_LEFT) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="reg-house">Nomor rumah <span class="required">*</span></label>
                            <input id="reg-house" name="house_number" required value="{{ old('house_number') }}" placeholder="Contoh: 12A">
                        </div>
                        <div class="field">
                            <label for="reg-household">Nama kepala keluarga (opsional)</label>
                            <input id="reg-household" name="household_name" value="{{ old('household_name') }}" placeholder="Contoh: Keluarga Sari">
                        </div>
                        <div class="field">
                            <label for="reg-password">Kata sandi <span class="required">*</span></label>
                            <input id="reg-password" name="password" type="password" minlength="8" maxlength="72" required>
                            <div class="field-hint">Minimal 8 karakter. Jangan gunakan tanggal lahir atau nomor rumah.</div>
                        </div>
                        <div class="field">
                            <label for="reg-password-confirm">Ulangi kata sandi <span class="required">*</span></label>
                            <input id="reg-password-confirm" name="password_confirmation" type="password" minlength="8" required>
                        </div>
                    </div>
                    <div class="callout warning gap-top"><div><strong>Sudah punya ID warga?</strong>Jangan membuat akun baru. Hubungi pengurus agar akun online ditautkan ke ID warga yang sudah ada.</div></div>
                    <div class="checkline gap-top">
                        <input type="checkbox" id="reg-consent" name="consent" value="1" required @checked(old('consent'))>
                        <label for="reg-consent">Saya memahami dan menyetujui penggunaan data ini untuk administrasi bank sampah dan kegiatan RW 13.</label>
                    </div>
                    <div class="form-actions">
                        <a class="btn outline" href="{{ route('login') }}">Kembali</a>
                        <button class="btn primary" type="submit">Kirim pendaftaran</button>
                    </div>
                    <p class="form-footnote">Pendaftaran tidak langsung memberi poin atau akses pengelolaan. Pengurus akan memeriksa data terlebih dahulu.</p>
                </form>
            </div>
            <aside class="card">
                <h3 class="card-title">Setelah mendaftar</h3>
                <div class="flow-step"><div class="step-number">1</div><div><h4>Data diperiksa</h4><p>Pengurus mengecek nama, email, nomor HP, RT, nomor rumah, dan kemungkinan profil ganda.</p></div></div>
                <div class="flow-step"><div class="step-number">2</div><div><h4>Akun disetujui atau ditolak</h4><p>Kalau datanya cocok, akun langsung bisa dipakai. Kalau tidak, pengurus menolak pendaftaran.</p></div></div>
                <div class="flow-step"><div class="step-number">3</div><div><h4>Mulai menggunakan</h4><p>Masuk menggunakan email atau nomor HP dan kata sandi yang dibuat saat pendaftaran.</p></div></div>
            </aside>
        </div>
    </section>
@endsection
