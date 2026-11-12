@extends('layouts.public')
@section('title', 'Kata sandi baru — SIRKULA RW13')
@section('content')
    <section class="public-section">
        <div class="public-section-head">
            <div class="eyebrow">Bantuan akun</div>
            <h1>Buat kata sandi baru</h1>
            <p>Gunakan kata sandi yang tidak sama dengan tanggal lahir atau nomor rumah.</p>
        </div>
        <div class="card" style="max-width:680px">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-grid">
                    <div class="field full">
                        <label for="reset-email">Email</label>
                        <input id="reset-email" name="email" type="email" required value="{{ old('email', $email) }}">
                    </div>
                    <div class="field">
                        <label for="reset-password">Kata sandi baru <span class="required">*</span></label>
                        <input id="reset-password" name="password" type="password" minlength="8" required>
                    </div>
                    <div class="field">
                        <label for="reset-password-confirm">Ulangi kata sandi <span class="required">*</span></label>
                        <input id="reset-password-confirm" name="password_confirmation" type="password" minlength="8" required>
                    </div>
                </div>
                <div class="form-actions">
                    <a class="btn outline" href="{{ route('login') }}">Batal</a>
                    <button class="btn primary" type="submit">Simpan kata sandi</button>
                </div>
            </form>
        </div>
    </section>
@endsection
