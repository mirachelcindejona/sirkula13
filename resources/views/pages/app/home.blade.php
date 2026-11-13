@extends('layouts.app')
@section('title', 'Beranda')
@section('heading', 'Beranda')
@section('eyebrow', 'SIRKULA-CERDAS RW 13')
@section('subtitle', 'Informasi bank sampah dan kegiatan lingkungan RW 13.')
@section('content')
    @php
        $first = trim(strtok(auth()->user()->name, '—'));
        $next = $upcomingEvents->first();
    @endphp
    <section class="hero">
        <div class="hero-inner">
            <div>
                <div class="eyebrow">Kelurahan Babakan Sari · Kecamatan Kiaracondong</div>
                <h2>Lingkungan terawat, kontribusi tercatat.</h2>
                <p>Halo, <strong>{{ $first }}</strong>. Kelola setoran, kegiatan, dan manfaat kontribusi lingkungan dalam satu tempat.</p>
                <div class="row">
                    @if ($role === 'warga' && $profile?->status === 'active')
                        <a class="btn primary" href="{{ route('citizen.deposits') }}"><x-icon name="scale" /> Lihat setoran saya</a>
                    @elseif ($role === 'warga')
                        <a class="btn primary" href="{{ route('public.events') }}"><x-icon name="dashboard" /> Lihat kegiatan</a>
                    @elseif ($role === 'operator-bank-sampah')
                        <a class="btn primary" href="{{ route('bank.deposits.create') }}"><x-icon name="scale" /> Buka menu utama</a>
                    @elseif ($role === 'bendahara')
                        <a class="btn primary" href="{{ route('finance.payouts') }}"><x-icon name="dashboard" /> Buka menu utama</a>
                    @else
                        <a class="btn primary" href="{{ route('admin.approvals') }}"><x-icon name="dashboard" /> Buka menu utama</a>
                    @endif
                    <a class="btn outline" href="{{ route('public.guide') }}"><x-icon name="book" /> Panduan pemilahan</a>
                </div>
            </div>
            <img class="hero-mark" src="{{ asset('images/logo.png') }}" alt="Logo SIRKULA-CERDAS">
        </div>
    </section>

    @if ($role === 'warga' && $profile?->status !== 'active')
        <div class="callout warning gap-top"><x-icon name="alert" /><div><strong>Pendaftaran sedang diperiksa</strong>Fitur yang memerlukan akun aktif akan terbuka setelah data disetujui.</div></div>
    @endif

    @if ($role === 'warga' && $profile?->status === 'active')
        <div class="grid three home-stats gap-top">
            <div class="card kpi"><div class="kpi-icon"><x-icon name="sparkles" /></div><div class="kpi-value">{{ number_format($profile->availablePoints(), 0, ',', '.') }}</div><div class="kpi-label">EcoPoints tersedia</div><div class="kpi-note">Untuk reward</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="award" /></div><div class="kpi-value">{{ number_format($profile->earnedPoints(), 0, ',', '.') }}</div><div class="kpi-label">Total terkumpul</div><div class="kpi-note">Rank {{ $profile->rank()['name'] }}</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="scale" /></div><div class="kpi-value">{{ $deposits->count() }}</div><div class="kpi-label">Setoran</div><div class="kpi-note">Riwayat setoran</div></div>
        </div>
        <div class="grid two gap-top">
            <div class="card">
                <h3 class="card-title">Yang ingin Anda lakukan?</h3>
                <p class="card-subtitle">Pilih menu yang dibutuhkan.</p>
                <div class="grid two home-actions gap-top">
                    <a class="btn primary block" href="{{ route('citizen.deposits') }}">Lihat setoran dan pembayaran</a>
                    <a class="btn secondary block" href="{{ route('citizen.points') }}">Tukar EcoPoints dengan reward</a>
                    <a class="btn outline block" href="{{ route('public.events') }}">Daftar kegiatan warga</a>
                    <a class="btn outline block" href="{{ route('public.catalog') }}">Lihat produk warga</a>
                </div>
            </div>
            <div class="card">
                <h3 class="card-title">{{ $next ? 'Kegiatan terdekat' : 'Jadwal kegiatan' }}</h3>
                @if ($next)
                    <div class="event-status-line gap-top"><span class="badge success">Pendaftaran tersedia</span><span class="point-pill">+{{ $next->attendance_points ?: 50 }} poin</span></div>
                    <h4>{{ $next->title }}</h4>
                    <p class="small muted">{{ $next->event_date?->locale('id')->translatedFormat('d M Y') }} · {{ $next->event_time }}<br>{{ $next->location }}</p>
                    <p class="small">Poin diberikan setelah kehadiran diverifikasi.</p>
                    <a class="btn outline block gap-top" href="{{ route('public.events') }}">Lihat kegiatan</a>
                @else
                    <p class="small muted">Belum ada kegiatan yang diumumkan.</p>
                @endif
            </div>
        </div>
    @elseif ($role === 'operator-bank-sampah')
        <div class="grid three gap-top">
            <div class="card kpi"><div class="kpi-icon"><x-icon name="scale" /></div><div class="kpi-value">{{ $todayDeposits }}</div><div class="kpi-label">Setoran hari ini</div><div class="kpi-note">Langsung masuk catatan</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="box" /></div><div class="kpi-value">{{ number_format($stockKg, 1, ',', '.') }} kg</div><div class="kpi-label">Total stok material</div><div class="kpi-note">Setoran diterima dikurangi keluar</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ $unpaidPayouts }}</div><div class="kpi-label">Tabungan belum cair</div><div class="kpi-note">Dicairkan saat jadwal pembagian</div></div>
        </div>
        <div class="grid two gap-top">
            <div class="card">
                <div class="card-head"><div><h3 class="card-title">Pekerjaan utama</h3><p class="card-subtitle">Pilih tindakan sesuai proses yang sedang berjalan.</p></div></div>
                <div class="stack">
                    <a class="btn primary block" href="{{ route('bank.deposits.create') }}"><x-icon name="scale" /> Catat setoran warga</a>
                    <a class="btn secondary block" href="{{ route('bank.records') }}"><x-icon name="receipt" /> Lihat catatan setoran</a>
                    <a class="btn outline block" href="{{ route('bank.inventory') }}"><x-icon name="box" /> Catat material keluar</a>
                    <a class="btn outline block" href="{{ route('finance.payouts') }}"><x-icon name="wallet" /> Pencairan setoran</a>
                </div>
            </div>
            <div class="card">
                <div class="card-head"><h3 class="card-title">Ingat sebelum menyimpan</h3></div>
                <div class="stack">
                    <div class="callout"><x-icon name="wallet" /><div><strong>Uang ditabung dulu</strong>Setoran jual tidak dibayar di tempat. Nilainya masuk tabungan dan dicairkan saat jadwal pembagian.</div></div>
                </div>
            </div>
        </div>
    @elseif ($role === 'pengelola-kegiatan')
        <div class="grid three gap-top">
            <div class="card kpi"><div class="kpi-icon"><x-icon name="calendar" /></div><div class="kpi-value">{{ $publishedEvents }}</div><div class="kpi-label">Kegiatan tayang</div><div class="kpi-note">Bisa dilihat warga</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="check" /></div><div class="kpi-value">{{ $pendingAttendance }}</div><div class="kpi-label">Kehadiran menunggu</div><div class="kpi-note">Perlu diverifikasi</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="box" /></div><div class="kpi-value">{{ $draftCatalog }}</div><div class="kpi-label">Katalog belum tayang</div><div class="kpi-note">Masih draf</div></div>
        </div>
        <div class="grid two gap-top">
            <div class="card">
                <h3 class="card-title">Kelola aktivitas warga</h3>
                <p class="card-subtitle">Kehadiran baru memberi poin setelah disahkan.</p>
                <div class="stack gap-top">
                    <a class="btn primary block" href="{{ route('program.events') }}">Buat kegiatan dan catat kehadiran</a>
                    <a class="btn outline block" href="{{ route('program.catalog') }}">Siapkan produk untuk ditinjau</a>
                </div>
            </div>
            <div class="callout warning"><x-icon name="alert" /><div><strong>Hadir belum berarti poin masuk</strong>Pendaftaran atau check-in belum memberikan poin. Verifikator berbeda perlu mengesahkan klaim agar poin dihitung satu kali.</div></div>
        </div>
    @elseif ($role === 'bendahara')
        <div class="grid three gap-top">
            <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($cashBalance) }}</div><div class="kpi-label">Saldo kas tercatat</div><div class="kpi-note">Dari kas masuk dikurangi kas keluar</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="receipt" /></div><div class="kpi-value">{{ $unpaidPayouts }}</div><div class="kpi-label">Tabungan warga</div><div class="kpi-note">Belum dicairkan penuh</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="heart" /></div><div class="kpi-value">{{ $pendingDonationCount }}</div><div class="kpi-label">Donasi menunggu</div><div class="kpi-note">Belum menjadi kas masuk</div></div>
        </div>
        <div class="grid two gap-top">
            <div class="card">
                <h3 class="card-title">Pekerjaan keuangan</h3>
                <p class="card-subtitle">Semua transaksi kas perlu tanggal, tujuan, dan bukti.</p>
                <div class="stack gap-top">
                    <a class="btn primary block" href="{{ route('finance.payouts') }}">Kelola pembayaran warga</a>
                    <a class="btn secondary block" href="{{ route('finance.donations') }}">Verifikasi donasi yang diterima</a>
                    <a class="btn outline block" href="{{ route('finance.cashbook') }}">Buka buku kas</a>
                    <a class="btn outline block" href="{{ route('finance.reports') }}">Lihat laporan</a>
                </div>
            </div>
            <div class="callout"><x-icon name="wallet" /><div><strong>Catatan langsung masuk buku</strong>Pemasukan dan pengeluaran yang sudah terjadi dicatat tanpa antrean persetujuan. Koreksi hanya untuk catatan yang baru dibuat.</div></div>
        </div>
    @else
        <div class="grid three gap-top">
            <div class="card kpi"><div class="kpi-icon"><x-icon name="user" /></div><div class="kpi-value">{{ $pendingApprovals }}</div><div class="kpi-label">Akun menunggu</div><div class="kpi-note">Periksa duplikasi sebelum aktif</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="alert" /></div><div class="kpi-value">{{ $held }}</div><div class="kpi-label">Pesanan ditahan</div><div class="kpi-note">Menunggu pemeriksaan</div></div>
            <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ $unpaidPayouts }}</div><div class="kpi-label">Tabungan belum cair</div><div class="kpi-note">Dicairkan saat jadwal pembagian</div></div>
        </div>
        <div class="grid two gap-top">
            <div class="card">
                <h3 class="card-title">Kontrol administrasi</h3>
                <p class="card-subtitle">Gunakan hak minimum dan catat alasan untuk tindakan penting.</p>
                <div class="stack gap-top">
                    <a class="btn primary block" href="{{ route('admin.approvals') }}">Periksa pendaftaran warga</a>
                    <a class="btn secondary block" href="{{ route('admin.tariff') }}">Tinjau harga dan aturan poin</a>
                    <a class="btn outline block" href="{{ route('program.events') }}">Kelola kegiatan</a>
                    <a class="btn outline block" href="{{ route('admin.audit') }}">Periksa audit log</a>
                </div>
            </div>
            <div class="callout warning"><x-icon name="alert" /><div><strong>Tarif dan keputusan RW</strong>Harga beli material belum disahkan RW. Setoran Jual perlu memakai tarif yang telah ditetapkan dan memiliki tanggal berlaku.</div></div>
        </div>
    @endif
@endsection
