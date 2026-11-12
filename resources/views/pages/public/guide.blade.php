@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Panduan pemilahan')
@section('heading', 'Panduan pemilahan')
@section('eyebrow', 'Mulai dari rumah')
@section('subtitle', 'Kenali material yang diterima, siapkan dengan benar, lalu pilih setoran jual atau donasi setelah melihat informasinya.')
@section('content')
    @guest
        <div class="public-subpage-head">
            <div class="eyebrow">SIRKULA-CERDAS · RW 13</div>
            <h1>Panduan pemilahan</h1>
            <p>Kenali material yang diterima, siapkan dengan benar, lalu pilih setoran jual atau donasi setelah melihat informasinya.</p>
        </div>
    @endguest

    <div class="callout {{ $priceOfficial ? 'success' : 'warning' }}">
        <x-icon :name="$priceOfficial ? 'check' : 'alert'" />
        <div>
            <strong>{{ $priceOfficial ? 'Harga beli material yang berlaku' : 'Harga beli material menunggu pengesahan' }}</strong>
            {{ $priceOfficial ? 'Nominal dihitung dari berat aktual yang diterima dan tarif yang berlaku.' : 'Sebelum memilih Jual, tanyakan tarif terbaru kepada petugas bank sampah.' }}
        </div>
    </div>

    <div class="card gap-top">
        <div class="card-head">
            <div>
                <h2 class="card-title">Material yang diterima</h2>
                <p class="card-subtitle">Material harus bersih, kering, dan sesuai standar penerimaan.</p>
            </div>
        </div>
        @foreach ($materials as $material)
            @php($rate = $material->rates->first())
            @php($sell = (int) (optional($rate)->eco_points_per_kg ?? 0))
            @php($donate = (int) round($sell * (float) (optional($rate)->donation_multiplier ?? 1.5)))
            <div class="material-row">
                <div class="material-icon">{{ $material->icon }}</div>
                <div>
                    <div class="material-title">{{ $material->name }}</div>
                    <div class="material-detail">{{ $material->acceptance_standard }}</div>
                    <div class="row gap-top">
                        <span class="point-pill">Jual {{ number_format($sell, 0, ',', '.') }} poin/kg</span>
                        <span class="point-pill">Donasi {{ number_format($donate, 0, ',', '.') }} poin/kg</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="tiny muted">Harga/kg</div>
                    <div class="strong">{{ $priceOfficial ? \App\Support\Labels::money($rate->buy_price_per_kg ?? 0) : 'Belum berlaku' }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <h2 class="section-title">Pilih Jual atau Donasi</h2>
    <div class="grid two">
        <div class="card">
            <div class="rank-badge"><x-icon name="wallet" /> Jual</div>
            <h3 class="card-title gap-top">Uang dan EcoPoints</h3>
            <p class="card-subtitle">Nilai uang dihitung dari berat diterima × harga beli yang berlaku. Poin diberikan setelah setoran diverifikasi.</p>
            <div class="callout success gap-top">Contoh hitung: 2 kg PET bening menghasilkan 160 EcoPoints. Nominal rupiah mengikuti tarif yang disahkan.</div>
        </div>
        <div class="card">
            <div class="rank-badge"><x-icon name="heart" /> Donasi</div>
            <h3 class="card-title gap-top">EcoPoints lebih besar</h3>
            <p class="card-subtitle">Material diberikan untuk mendukung kegiatan lingkungan. Tidak ada pembayaran uang kepada penyetor.</p>
            <div class="callout gap-top">Contoh hitung: 2 kg PET bening menghasilkan 240 EcoPoints.</div>
        </div>
    </div>

    <h2 class="section-title">Bagaimana setoran diproses?</h2>
    <div class="card">
        @foreach ([
            ['1', 'Konfirmasi pilihan', 'Petugas menjelaskan pilihan Jual atau Donasi, harga, dan poin sebelum menimbang.'],
            ['2', 'Periksa dan timbang', 'Berat yang dicatat adalah berat aktual material yang diterima. Material yang tidak sesuai standar dapat ditolak atau diminta dipilah ulang.'],
            ['3', 'Verifikasi setoran', 'Petugas lain memeriksa transaksi sebelum stok dan EcoPoints diperbarui.'],
            ['4', 'Pembayaran setoran jual', 'Pembayaran dicatat terpisah dan berstatus menunggu sampai uang benar-benar dibayarkan.'],
        ] as [$number, $title, $text])
            <div class="flow-step">
                <div class="step-number">{{ $number }}</div>
                <div><h4>{{ $title }}</h4><p>{{ $text }}</p></div>
            </div>
        @endforeach
    </div>
@endsection
