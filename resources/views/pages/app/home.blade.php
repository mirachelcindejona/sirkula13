@extends('layouts.app')
@section('title', 'Beranda')
@section('heading', 'Beranda')
@section('eyebrow', 'SIRKULA-CERDAS RW 13')
@section('subtitle', auth()->user()->primaryRoleSlug() === 'warga' ? '' : 'Informasi bank sampah dan kegiatan lingkungan RW 13.')
@section('content')
    @php
        $first = trim(strtok(auth()->user()->name, '—'));
        $next = $upcomingEvents->first();
    @endphp
    @if ($role === 'warga')
        @php
            $approvedWarga = $profile?->status === 'active';
            $available = $profile?->availablePoints() ?? 0;
            $total = $profile?->earnedPoints() ?? 0;
            $rank = $profile?->rank() ?? \App\Support\EcoRank::forTotal(0);
            $toNext = $rank['next'] ? max(0, $rank['next'] - $total) : 0;
            $pct = $rank['next'] ? min(100, (($total - $rank['min']) / max(1, $rank['next'] - $rank['min'])) * 100) : 100;
        @endphp
        <section class="warga-hero">
            <div class="warga-hero-text">
                <h2>Halo, {{ $first }}!</h2>
                <p>{{ $approvedWarga ? 'Setor barang bekas, kumpulkan poin, lalu tukar dengan hadiah.' : 'Akun Anda sedang dicek pengurus. Sambil menunggu, lihat-lihat dulu ya.' }}</p>
                <div class="row">
                    <a class="btn primary" href="{{ route('citizen.points') }}"><x-icon name="gift" /> Tukar poin</a>
                    <a class="btn outline" href="{{ route('public.guide') }}"><x-icon name="book" /> Cara memilah</a>
                </div>
            </div>
            <div class="warga-rank">
                <div class="warga-rank-top">
                    <span class="warga-rank-medal" aria-hidden="true">{{ $rank['emoji'] }}</span>
                    <div class="grow">
                        <span class="warga-rank-label">Rank Anda</span>
                        <strong>{{ $rank['name'] }}</strong>
                    </div>
                    <x-rank-guide :current="$rank['name']" />
                </div>
                <div class="warga-rank-bar"><span style="width:{{ number_format($pct, 2, '.', '') }}%"></span></div>
                <p>{{ $rank['next'] ? number_format($toNext, 0, ',', '.').' poin lagi untuk naik rank' : 'Anda sudah di rank tertinggi. Keren!' }}</p>
                <div class="warga-rank-stats">
                    <div><strong>{{ number_format($available, 0, ',', '.') }}</strong><span>Poin siap ditukar</span></div>
                    <div><strong>{{ number_format($total, 0, ',', '.') }}</strong><span>Total poin</span></div>
                </div>
            </div>
        </section>
    @else
    <section class="hero">
        <div class="hero-inner">
            <div>
                <div class="eyebrow">Kelurahan Babakan Sari · Kecamatan Kiaracondong</div>
                <h2>Lingkungan terawat, kontribusi tercatat.</h2>
                <p>Halo, <strong>{{ $first }}</strong>. Kelola setoran, kegiatan, dan manfaat kontribusi lingkungan dalam satu tempat.</p>
                <div class="row">
                    @if ($role === 'warga')
                        <a class="btn primary" href="{{ route('citizen.deposits') }}"><x-icon name="scale" /> Lihat setoran saya</a>
                    @elseif ($role === 'operator-bank-sampah')
                        <a class="btn primary" href="{{ route('bank.deposits.create') }}"><x-icon name="scale" /> Buka menu utama</a>
                    @elseif ($role === 'bendahara')
                        <a class="btn primary" href="{{ route('finance.payouts') }}"><x-icon name="dashboard" /> Buka menu utama</a>
                    @elseif ($role === 'pengelola-kegiatan')
                        <a class="btn primary" href="{{ route('program.events') }}"><x-icon name="calendar" /> Buka kegiatan</a>
                    @else
                        <a class="btn primary" href="{{ route('admin.approvals') }}"><x-icon name="dashboard" /> Buka menu utama</a>
                    @endif
                    <a class="btn outline" href="{{ route('public.guide') }}"><x-icon name="book" /> Panduan pemilahan</a>
                </div>
            </div>
            <img class="hero-mark" src="{{ asset('images/logo.png') }}" alt="Logo SIRKULA-CERDAS">
        </div>
    </section>
    @endif

    @if ($role === 'warga')
        <div class="quick-tiles gap-top">
            <a class="quick-tile" href="{{ route('citizen.deposits') }}"><span class="quick-icon"><x-icon name="scale" /></span><strong>Setoran saya</strong><small>{{ $deposits->isEmpty() ? 'Belum ada' : 'Lihat riwayat' }}</small></a>
            <a class="quick-tile" href="{{ route('citizen.points') }}"><span class="quick-icon"><x-icon name="gift" /></span><strong>Tukar hadiah</strong><small>{{ number_format($available, 0, ',', '.') }} poin</small></a>
            <a class="quick-tile" href="{{ route('public.events') }}"><span class="quick-icon"><x-icon name="calendar" /></span><strong>Kegiatan</strong><small>Dapat poin</small></a>
            <a class="quick-tile" href="{{ route('citizen.products') }}"><span class="quick-icon"><x-icon name="box" /></span><strong>Jualan saya</strong><small>Pasang produk</small></a>
        </div>

        <div class="section-head">
            <div>
                <h2 class="section-title">Lagi dijual tetangga</h2>
                <p class="muted small">Barang buatan warga RW 13. Pesan langsung lewat WhatsApp.</p>
            </div>
            <a class="btn outline small-btn" href="{{ route('public.catalog') }}">Lihat semua <x-icon name="arrow-right" /></a>
        </div>
        @if ($shelf->isEmpty())
            <div class="card empty-state">
                <div class="empty-icon"><x-icon name="box" /></div>
                <h3>Belum ada yang jualan</h3>
                <p>Jadi yang pertama? Pasang produk buatan Anda, tetangga bisa langsung pesan.</p>
                <a class="btn primary" href="{{ route('citizen.products') }}">Pasang produk</a>
            </div>
        @else
            <div class="shelf-grid">
                @foreach ($shelf as $item)
                    @php($photo = $item->image_path ? \App\Support\Uploads::url($item->image_path) : asset(match ($item->image_key) { 'bag' => 'images/products/bag.jpg', 'decor' => 'images/products/decor.jpg', default => 'images/products/pot.jpg' }))
                    @php($mine = $profile && (int) $item->seller_profile_id === (int) $profile->id)
                    <a class="shelf-card" href="{{ route('public.catalog') }}#produk-{{ $item->id }}">
                        <div class="shelf-photo">
                            <img src="{{ $photo }}" alt="Foto {{ $item->name }}" loading="lazy">
                            @if ($mine)<span class="shelf-tag">Produk Anda</span>@endif
                        </div>
                        <div class="shelf-body">
                            <h3>{{ $item->name }}</h3>
                            <p class="small muted">{{ $item->seller?->full_name ?: ($item->contact_name ?: 'Pengurus RW') }}</p>
                            <strong class="shelf-price">{{ \App\Support\Labels::money($item->price) }}</strong>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($next)
            <div class="next-event gap-top">
                <span class="next-event-date">
                    <strong>{{ $next->event_date?->format('d') }}</strong>
                    <small>{{ $next->event_date?->locale('id')->translatedFormat('M') }}</small>
                </span>
                <div class="grow">
                    <p class="eyebrow">Kegiatan terdekat</p>
                    <h3>{{ $next->title }}</h3>
                    <p class="small muted">{{ $next->event_time }} · {{ $next->location }}</p>
                </div>
                <span class="point-pill">+{{ $next->attendance_points ?: 50 }} poin</span>
                <a class="btn primary small-btn" href="{{ route('public.events') }}">Ikut</a>
            </div>
        @endif
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
                    <a class="btn outline block" href="{{ route('program.orders') }}">Pantau pesanan produk</a>
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
