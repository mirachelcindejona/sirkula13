@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Donasi dan dukungan')
@section('heading', 'Donasi dan dukungan')
@section('eyebrow', 'Dukungan yang transparan')
@section('subtitle', 'Donasi uang baru dicatat sebagai kas dan SupportPoints setelah uang diterima serta diverifikasi.')
@section('content')
    @php($name = auth()->user()?->residentProfile?->status === 'active' ? auth()->user()->name : '')
    @guest
        <div class="public-subpage-head">
            <div class="eyebrow">SIRKULA-CERDAS · RW 13</div>
            <h1>Donasi dan dukungan</h1>
            <p>Donasi uang baru dicatat sebagai kas dan SupportPoints setelah uang diterima serta diverifikasi.</p>
        </div>
    @endguest

    <div class="grid two">
        <div class="card">
            <h3 class="card-title">Dukung kegiatan RW 13</h3>
            <p class="card-subtitle">Donasi digunakan sesuai tujuan yang disepakati dan dicatat dalam buku kas.</p>
            <div class="kpi-value gap-top">{{ \App\Support\Labels::money($total) }}</div>
            <div class="kpi-label">Total donasi terverifikasi · data tersimpan</div>
            <div class="divider"></div>
            <div class="flow-step"><div class="step-number">1</div><div><h4>Isi informasi donasi</h4><p>Nominal, tujuan, serta nama atau alias tampilan.</p></div></div>
            <div class="flow-step"><div class="step-number">2</div><div><h4>Petugas mengonfirmasi uang diterima</h4><p>Janji atau laporan transfer yang belum diperiksa tidak dianggap sebagai kas.</p></div></div>
            <div class="flow-step"><div class="step-number">3</div><div><h4>SupportPoints dicatat terpisah</h4><p>1 poin per Rp1.000 setelah donasi diterima dan diverifikasi. Tidak menambah rank EcoPoints.</p></div></div>
        </div>
        <div class="card">
            <div class="card-head">
                <div>
                    <h3 class="card-title">Donasi uang</h3>
                    <p class="card-subtitle">Formulir ini mencatat niat donasi. Bendahara akan memeriksa penerimaan sebelum donasi masuk ke buku kas.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('public.donation.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="donor-name">Nama donatur <span class="required">*</span></label>
                        <input id="donor-name" name="donorName" required value="{{ old('donorName', $name) }}" placeholder="Nama sesuai keinginan">
                    </div>
                    <div class="field">
                        <label for="donor-amount">Nominal donasi (Rp) <span class="required">*</span></label>
                        <input id="donor-amount" name="amount" type="number" min="1000" step="1000" required value="{{ old('amount') }}" placeholder="Contoh: 50000">
                    </div>
                    <div class="field full">
                        <label for="donor-purpose">Tujuan donasi <span class="required">*</span></label>
                        <select id="donor-purpose" name="purpose" required>
                            <option value="">Pilih tujuan</option>
                            @foreach (['Kegiatan lingkungan', 'Reward warga', 'Operasional dan perawatan sistem', 'Penggunaan lain yang disepakati RW'] as $purpose)
                                <option @selected(old('purpose') === $purpose)>{{ $purpose }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="donor-display">Nama di leaderboard</label>
                        <select id="donor-display" name="display">
                            <option value="Alias" @selected(old('display', 'Alias') === 'Alias')>Alias</option>
                            <option value="Name" @selected(old('display') === 'Name')>Nama</option>
                            <option value="Anonim" @selected(old('display') === 'Anonim')>Anonim</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="donor-alias">Alias (jika dipilih)</label>
                        <input id="donor-alias" name="alias" value="{{ old('alias') }}" placeholder="Misalnya Sahabat RW 13">
                    </div>
                    <div class="field full">
                        <div class="checkline">
                            <input id="donor-confirm" name="confirm" type="checkbox" value="1" required @checked(old('confirm'))>
                            <label for="donor-confirm">Saya memahami donasi baru dicatat sebagai kas dan SupportPoints setelah petugas memverifikasi bahwa uang benar-benar diterima.</label>
                        </div>
                    </div>
                </div>
                <div class="form-actions"><button class="btn primary" type="submit">Kirim catatan donasi</button></div>
            </form>
        </div>
    </div>
    <h2 class="section-title">Alur verifikasi</h2>
    <div class="card">
        <p class="small muted">Sesudah formulir ini dibuat, buka dengan akun Bendahara → <strong>Verifikasi donasi</strong>. Petugas memasukkan referensi bukti, lalu mengonfirmasi penerimaan. Barulah kas masuk dan SupportPoints ditambahkan.</p>
    </div>
@endsection
