@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Profil RW 13')
@section('heading', 'Profil RW 13')
@section('eyebrow', 'Mengenal lingkungan kami')
@section('subtitle', 'Informasi program pengelolaan sampah dan partisipasi warga.')
@section('content')
    @guest
        <div class="public-subpage-head">
            <div class="eyebrow">SIRKULA-CERDAS · RW 13</div>
            <h1>Profil RW 13</h1>
            <p>Informasi program pengelolaan sampah dan partisipasi warga.</p>
        </div>
    @endguest
    <section class="public-profile-hero">
        <div>
            <div class="eyebrow">Profil lingkungan</div>
            <h1>RW 13, Kelurahan Babakan Sari</h1>
            <p>Kecamatan Kiaracondong, Kota Bandung</p>
            <p>Program SIRKULA-CERDAS mendukung kegiatan bank sampah yang sudah berjalan melalui pemilahan dari rumah, pencatatan yang lebih tertib, partisipasi warga, dan pemanfaatan material.</p>
        </div>
        <img src="{{ asset('images/logo.png') }}" alt="Logo SIRKULA-CERDAS">
    </section>
    <div class="public-profile-content">
        <section>
            <h2>Tentang SIRKULA-CERDAS</h2>
            <p>Program ini membantu warga memahami material yang dapat disetorkan dan membantu pengelola mencatat material masuk, material keluar, pembayaran, EcoPoints, kegiatan, produk, serta dukungan dana.</p>
        </section>
        <section class="public-profile-cards">
            <article><span><x-icon name="scale" /></span><h3>Bank sampah</h3><p>Material diperiksa dan ditimbang. Warga memilih menjual atau mendonasikan material sebelum transaksi diproses.</p></article>
            <article><span><x-icon name="users" /></span><h3>Partisipasi warga</h3><p>Warga dapat mengikuti kerja bakti, pelatihan, dan kegiatan lingkungan lainnya.</p></article>
            <article><span><x-icon name="clipboard" /></span><h3>Pencatatan terbuka</h3><p>Setiap setoran, perubahan poin, penukaran reward, dan transaksi keuangan memiliki riwayat pencatatan.</p></article>
        </section>
        <section>
            <h2>Material yang biasa diterima</h2>
            <p>Jenis material yang diterima antara lain kardus atau karton bersih dan kering, botol PET, galon plastik yang sesuai, kaleng aluminium, kaleng besi atau seng, serta plastik keras lainnya yang memenuhi standar.</p>
            <a class="btn primary" href="{{ route('public.guide') }}">Lihat syarat material</a>
        </section>
        <section class="public-profile-note">
            <strong>Ingin ikut berpartisipasi?</strong>
            <p>Lihat jadwal setoran, panduan pemilahan, kegiatan warga, atau produk yang tersedia.</p>
            <div class="row">
                <a class="btn secondary" href="{{ route('public.events') }}">Lihat kegiatan</a>
                <a class="btn outline" href="{{ route('public.catalog') }}">Lihat produk</a>
            </div>
        </section>
    </div>
@endsection
