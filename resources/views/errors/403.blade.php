@extends('layouts.public')
@section('title', 'Tidak bisa dibuka')
@section('content')
    <section class="public-section">
        <div class="card" style="max-width:640px;margin:24px auto">
            <div class="eyebrow">Akses terbatas</div>
            <h1>Halaman ini tidak bisa dibuka</h1>
            <p>{{ $message }}</p>
            <div class="form-actions">
                <a class="btn primary" href="{{ auth()->check() ? route(\App\Support\Home::routeName(auth()->user())) : route('public.home') }}">Kembali ke beranda</a>
            </div>
        </div>
    </section>
@endsection
