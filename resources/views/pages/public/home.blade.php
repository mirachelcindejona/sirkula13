@extends('layouts.public')
@section('title', 'SIRKULA RW13 · RW 13')
@section('content')
    <section class="public-hero">
        <div class="public-hero-copy">
            <div class="public-location">RW 13 · Babakan Sari · Kiaracondong · Bandung</div>
            <h1>Kelola sampah bersama, mulai dari rumah.</h1>
            <p>Kenali bank sampah RW 13, lihat cara menyetorkan material, ikuti kegiatan lingkungan, dan temukan produk hasil pemanfaatan sampah warga.</p>
            <div class="public-hero-actions">
                <a class="btn primary" href="{{ route('public.guide') }}">Lihat panduan pemilahan</a>
                <a class="btn outline" href="{{ route('public.events') }}">Lihat kegiatan warga</a>
            </div>
            <div class="public-hero-note">Terbuka untuk warga RW 13 dan masyarakat yang ingin mendukung kegiatan lingkungan.</div>
        </div>
        <div class="public-hero-art">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIRKULA-CERDAS">
            <div class="hero-art-caption">Sampah terpilah · Manfaat bertambah</div>
        </div>
    </section>
    <section class="public-intro-grid">
        <div>
            <div class="eyebrow">Tentang program</div>
            <h2>Bank sampah yang lebih mudah diikuti dan tercatat.</h2>
            <p>Warga dapat memilah material dari rumah, menyetorkannya ke bank sampah, lalu memilih untuk menjual atau mendonasikannya. Setoran yang diterima langsung tercatat beserta poinnya.</p>
            <a class="text-link" href="{{ route('public.profile') }}">Kenali program RW 13 <x-icon name="arrow-right" /></a>
        </div>
        <div class="public-fact-card">
            <div class="public-fact"><strong>Jual atau donasi</strong><span>Warga menentukan pilihan sebelum setoran diproses.</span></div>
            <div class="public-fact"><strong>Uang dan poin</strong><span>Setoran jual memiliki nilai rupiah dan EcoPoints; donasi material memperoleh bonus EcoPoints.</span></div>
            <div class="public-fact"><strong>Catatan yang jelas</strong><span>Setoran, stok, pembayaran, dan poin dicatat secara terpisah.</span></div>
        </div>
    </section>
    <section class="public-section">
        <div class="section-heading-row"><div><div class="eyebrow">Layanan RW 13</div><h2>Mulai dari yang Anda butuhkan</h2></div></div>
        <div class="public-service-grid">
            <a class="public-service-card" href="{{ route('public.guide') }}"><span class="public-service-icon"><x-icon name="scale" /></span><strong>Bank sampah</strong><span>Kenali material yang diterima dan cara menyetorkannya.</span><em>Pelajari panduan <x-icon name="arrow-right" /></em></a>
            <a class="public-service-card" href="{{ route('public.leaderboard') }}"><span class="public-service-icon yellow"><x-icon name="sparkles" /></span><strong>EcoPoints & reward</strong><span>Poin dari setoran yang diterima dapat ditukar dengan reward yang tersedia.</span><em>Lihat peringkat <x-icon name="arrow-right" /></em></a>
            <a class="public-service-card" href="{{ route('public.events') }}"><span class="public-service-icon orange"><x-icon name="calendar" /></span><strong>Kegiatan warga</strong><span>Ikuti kerja bakti, pelatihan, dan kegiatan lingkungan.</span><em>Lihat kegiatan <x-icon name="arrow-right" /></em></a>
            <a class="public-service-card" href="{{ route('public.catalog') }}"><span class="public-service-icon brown"><x-icon name="box" /></span><strong>Produk warga</strong><span>Temukan produk hasil pemanfaatan material dan hubungi penjual.</span><em>Buka katalog <x-icon name="arrow-right" /></em></a>
            <a class="public-service-card" href="{{ route('public.donation') }}"><span class="public-service-icon"><x-icon name="heart" /></span><strong>Donasi & sponsorship</strong><span>Program terbuka untuk donasi masyarakat dan dukungan sponsor.</span><em>Lihat cara mendukung <x-icon name="arrow-right" /></em></a>
        </div>
    </section>
    <section class="public-section public-how">
        <div class="public-how-copy">
            <div class="eyebrow">Cara berpartisipasi</div>
            <h2>Empat langkah sederhana</h2>
            <p>Petugas akan membantu warga yang belum menggunakan HP. ID warga dapat dipakai untuk pencatatan setoran dan kehadiran.</p>
            <a class="btn secondary" href="{{ route('public.guide') }}">Baca panduan lengkap</a>
        </div>
        <div class="public-steps">
            <div><span>01</span><section><strong>Pilah dari rumah</strong><p>Pisahkan kardus, botol, galon, kaleng, dan material lain yang diterima.</p></section></div>
            <div><span>02</span><section><strong>Datang ke bank sampah</strong><p>Petugas memeriksa kondisi material dan menimbang berat yang diterima.</p></section></div>
            <div><span>03</span><section><strong>Pilih jual atau donasi</strong><p>Setoran jual mendapat pembayaran sesuai tarif yang berlaku dan EcoPoints. Donasi material mendapat bonus poin.</p></section></div>
            <div><span>04</span><section><strong>Pantau catatan kontribusi</strong><p>Riwayat setoran dan poin dapat dilihat melalui akun warga setelah petugas menyimpan catatan.</p></section></div>
        </div>
    </section>
    <section class="public-section">
        <div class="section-heading-row">
            <div><div class="eyebrow">Agenda lingkungan</div><h2>Kegiatan warga</h2><p class="section-desc">Lihat kegiatan yang sedang dibuka untuk pendaftaran.</p></div>
            <a class="btn outline" href="{{ route('public.events') }}">Semua kegiatan</a>
        </div>
        <div class="public-event-grid">
            @forelse ($events as $event)
                <article class="public-event-card">
                    <div class="event-date-block"><strong>{{ $event->event_date?->format('d') }}</strong><span>{{ $event->event_date?->translatedFormat('M') }}</span></div>
                    <div>
                        <h3>{{ $event->title }}</h3>
                        <p>{{ $event->event_date?->translatedFormat('d M Y') }} · {{ $event->event_time }}</p>
                        <small>{{ $event->location }}</small>
                    </div>
                    <span class="point-pill">+{{ $event->attendance_points ?: 50 }} poin</span>
                </article>
            @empty
                <div class="public-empty">Belum ada kegiatan yang diumumkan.</div>
            @endforelse
        </div>
    </section>
    <section class="public-section">
        <div class="section-heading-row">
            <div><div class="eyebrow">Karya warga</div><h2>Produk hasil pemanfaatan</h2><p class="section-desc">Pembelian dilakukan langsung dengan penjual. EcoPoints tidak digunakan sebagai alat pembayaran produk.</p></div>
            <a class="btn outline" href="{{ route('public.catalog') }}">Lihat katalog</a>
        </div>
        <div class="grid three">
            @forelse ($products as $product)
                <article class="card item-card">
                    @php($photo = $product->image_path ? \App\Support\Uploads::url($product->image_path) : asset(match ($product->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' }))
                    <div class="item-art product-photo"><img src="{{ $photo }}" alt="Foto {{ $product->name }}"></div>
                    <div>
                        <div class="row between"><span class="badge info">Tersedia</span><span class="small muted">Stok {{ $product->stock }}</span></div>
                        <h3 class="gap-top">{{ $product->name }}</h3>
                        <p>{{ $product->description }}</p>
                    </div>
                    <div class="price">{{ \App\Support\Labels::money($product->price) }}</div>
                </article>
            @empty
                <div class="public-empty">Produk akan ditampilkan setelah tersedia dan disetujui untuk dipublikasikan.</div>
            @endforelse
        </div>
    </section>
    <section class="public-section leaderboard-teaser">
        <div class="section-heading-row">
            <div><div class="eyebrow">Apresiasi warga</div><h2>Warga paling aktif</h2><p class="section-desc">Peringkat berdasarkan total EcoPoints dari aktivitas yang sudah diverifikasi.</p></div>
            <a class="btn outline" href="{{ route('public.leaderboard') }}">Lihat semua peringkat</a>
        </div>
        <div class="podium-grid">
            @foreach ($leaders as $index => $leader)
                @php($rank = \App\Support\EcoRank::forTotal((int) $leader->earned_points))
                <article class="podium-card podium-{{ $index + 1 }}">
                    <div class="podium-medal">{{ $index === 0 ? '🏆' : ($index === 1 ? '🥈' : '🥉') }}</div>
                    <div class="podium-rank">#{{ $index + 1 }}</div>
                    <h3>{{ $leader->display_name ?: $leader->full_name }}</h3>
                    <p>{{ $rank['name'] }}</p>
                    <strong>{{ number_format((int) $leader->earned_points, 0, ',', '.') }} EcoPoints</strong>
                </article>
            @endforeach
        </div>
    </section>
    <section class="public-join">
        <div>
            <div class="eyebrow">Warga RW 13</div>
            <h2>Ingin melihat riwayat setoran dan EcoPoints Anda?</h2>
            <p>Masuk ke akun untuk mendaftar kegiatan, memantau setoran, dan menukar poin dengan reward.</p>
        </div>
        <div class="public-join-actions">
            <a class="btn primary" href="{{ route('login') }}">Masuk ke akun</a>
            <a class="btn outline" href="{{ route('register') }}">Daftar sebagai warga</a>
        </div>
    </section>
    <section class="public-section">
        <div class="section-heading-row"><div><div class="eyebrow">Terbuka untuk umum</div><h2>Donasi dan sponsorship</h2></div></div>
        @include('pages.public._donation-channel', ['channel' => $channel])
    </section>
    <div class="public-contact-strip">
        <div><x-icon name="map-pin" /><span><strong>RW 13, Kelurahan Babakan Sari</strong><small>Kecamatan Kiaracondong, Kota Bandung</small></span></div>
        <p>Untuk jadwal setoran dan informasi layanan, silakan tanyakan kepada pengurus RW atau petugas bank sampah.</p>
    </div>
@endsection
