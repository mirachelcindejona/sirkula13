@extends('layouts.public')
@section('title', 'Masuk — SIRKULA RW13')
@section('content')
    <section class="login-layout">
        <div class="login-aside">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIRKULA RW13">
            <div class="eyebrow">Akun SIRKULA RW13</div>
            <h1>Selamat datang kembali.</h1>
            <p>Masuk untuk melihat catatan setoran, EcoPoints, kegiatan, dan layanan sesuai akses akun Anda.</p>
            <div class="login-aside-note">
                <strong>Belum memiliki akun?</strong>
                <span>Warga RW 13 dapat mendaftar dengan email aktif. Akun dapat digunakan setelah data diperiksa pengurus.</span>
                <a class="btn secondary" href="{{ route('register') }}">Daftar sebagai warga</a>
            </div>
        </div>
        <div class="login-card">
            <h2>Masuk ke akun</h2>
            <p class="muted">Gunakan email atau nomor HP dan kata sandi yang terdaftar.</p>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="login-identifier">Email atau nomor HP</label>
                    <input id="login-identifier" name="login" type="text" autocomplete="username" placeholder="nama@email.com atau 08xxxxxxxxxx" value="{{ old('login') }}" required>
                    @error('login')<div class="field-hint">{{ $message }}</div>@enderror
                </div>
                <div class="field gap-top">
                    <label for="login-password">Kata sandi</label>
                    <input id="login-password" name="password" type="password" autocomplete="current-password" required placeholder="Masukkan kata sandi">
                </div>
                <button class="btn primary block gap-top" type="submit">Masuk</button>
            </form>
            <a class="text-link gap-top" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            <a class="text-link login-back" href="{{ route('public.home') }}"><x-icon name="arrow-left" /> Kembali ke halaman utama</a>
            <div class="login-help">Akun pengelola diberikan oleh Superadmin sesuai tugas masing-masing.</div>
        </div>
    </section>
@endsection
