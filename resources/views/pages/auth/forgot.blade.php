@extends('layouts.public')
@section('title', 'Lupa kata sandi — SIRKULA RW13')
@section('content')
    <section class="public-section">
        <div class="public-section-head">
            <div class="eyebrow">Bantuan akun</div>
            <h1>Lupa kata sandi?</h1>
            <p>Masukkan email yang terdaftar. Petunjuk pemulihan dikirim ke email itu.</p>
        </div>
        <div class="card" style="max-width:680px">
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="field">
                    <label for="reset-email">Email terdaftar <span class="required">*</span></label>
                    <input id="reset-email" name="email" type="email" required value="{{ old('email') }}" autocomplete="email">
                    @error('email')<div class="field-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-actions">
                    <a class="btn outline" href="{{ route('login') }}">Batal</a>
                    <button class="btn primary" type="submit">Kirim petunjuk</button>
                </div>
            </form>
        </div>
    </section>
@endsection
