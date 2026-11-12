@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Peringkat & dukungan')
@section('heading', 'Peringkat & dukungan')
@section('eyebrow', 'Lihat kontribusi dan reward')
@section('subtitle', 'EcoPoints warga dan SupportPoints donatur uang ditampilkan terpisah agar tidak tertukar.')
@section('content')
    @guest
        <div class="public-subpage-head">
            <div class="eyebrow">SIRKULA-CERDAS · RW 13</div>
            <h1>Peringkat & dukungan</h1>
            <p>EcoPoints warga dan SupportPoints donatur uang ditampilkan terpisah agar tidak tertukar.</p>
        </div>
    @endguest

    <div class="callout">
        <x-icon name="award" />
        <div><strong>Ada dua daftar yang berbeda</strong>EcoPoints mencerminkan kontribusi lingkungan warga. SupportPoints menghargai donasi uang yang telah diverifikasi. SupportPoints tidak dapat ditukar dan tidak menaikkan rank warga.</div>
    </div>

    <div class="grid two gap-top">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3 class="card-title">Leaderboard EcoPoints</h3>
                    <p class="card-subtitle">Total poin terkumpul; saldo yang ditukar tidak mengurangi rank.</p>
                </div>
                <span class="badge info">Warga RW 13</span>
            </div>
            @foreach ($rows as $row)
                @php($rank = \App\Support\EcoRank::forTotal((int) $row->earned_points))
                <div class="list-row">
                    <div class="step-number" style="width:31px;height:31px;border-radius:10px;background:{{ $loop->first ? 'var(--yellow)' : ($loop->iteration === 2 ? 'var(--green-soft)' : '#F0F1E9') }};color:var(--ink)">{{ $loop->iteration }}</div>
                    <div class="grow">
                        <h4>{{ $row->display_name ?: $row->full_name }}</h4>
                        <p>{{ $rank['name'] }} · saldo tersedia {{ number_format(max(0, (int) $row->point_balance), 0, ',', '.') }} poin</p>
                    </div>
                    <span class="point-pill">{{ number_format((int) $row->earned_points, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
        <div class="card">
            <div class="card-head">
                <div>
                    <h3 class="card-title">Leaderboard SupportPoints</h3>
                    <p class="card-subtitle">1 poin per Rp1.000 donasi uang yang benar-benar diterima.</p>
                </div>
                <span class="badge donation">Donasi uang</span>
            </div>
            @forelse ($supporters as $donation)
                @php($display = $donation->display_mode_snapshot === 'anonim' ? 'Donatur anonim' : ($donation->display_mode_snapshot === 'alias' ? ($donation->alias_snapshot ?: 'Sahabat RW 13') : $donation->donor_name_snapshot))
                @php($points = $donation->support_points_awarded ?: (int) floor(((float) $donation->amount) / 1000))
                <div class="list-row">
                    <div class="step-number" style="width:31px;height:31px;border-radius:10px;background:#FFF3A2;color:var(--brown)">{{ $loop->iteration }}</div>
                    <div class="grow">
                        <h4>{{ $display }}</h4>
                        <p>Kontribusi yang sudah diverifikasi</p>
                    </div>
                    <span class="point-pill">{{ number_format($points, 0, ',', '.') }} SP</span>
                </div>
            @empty
                <p class="small muted">Belum ada donasi uang yang diverifikasi.</p>
            @endforelse
            <div class="callout warning gap-top">
                <x-icon name="shield" />
                <div><strong>Privasi donatur</strong>Donatur dapat memilih nama, alias, atau anonim. Sponsor/badan usaha dicatat terpisah dari peringkat individu.</div>
            </div>
        </div>
    </div>
@endsection
