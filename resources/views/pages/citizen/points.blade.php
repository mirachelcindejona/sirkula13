@extends('layouts.app')
@section('title', 'Poin & hadiah')
@section('heading', 'Poin & hadiah')
@section('eyebrow', 'EcoPoints Anda')
@section('subtitle', 'Tukar poin dengan hadiah yang masih ada stoknya. Hadiah diambil di bank sampah RW.')
@section('content')
    @php
        $total = $profile?->earnedPoints() ?? 0;
        $available = $profile?->availablePoints() ?? 0;
        $rank = $profile?->rank() ?? ['name' => 'Bronze', 'emoji' => '🥉', 'min' => 0, 'next' => 1000];
        $next = $rank['next'] ?? null;
        $pct = $next ? min(100, (($total - $rank['min']) / max(1, $next - $rank['min'])) * 100) : 100;
    @endphp
    <div class="grid two">
        <div class="card">
            <div class="row between">
                <div>
                    <div class="eyebrow">Poin siap ditukar</div>
                    <div class="kpi-value">{{ number_format($available, 0, ',', '.') }}</div>
                </div>
                <div class="rank-badge"><span class="rank-medal">{{ $rank['emoji'] }}</span><span>{{ $rank['name'] }}</span><x-rank-guide :current="$rank['name']" /></div>
            </div>
            <div class="divider"></div>
            <div class="row between"><span class="small muted">Total poin selama ini</span><strong>{{ number_format($total, 0, ',', '.') }} poin</strong></div>
            <div class="rank-progress"><span style="width:{{ $pct }}%"></span></div>
            <p class="small muted">{{ $next ? number_format(max(0, $next - $total), 0, ',', '.').' poin lagi untuk naik rank.' : 'Anda sudah di rank tertinggi.' }} Menukar hadiah tidak menurunkan rank.</p>
        </div>
        <div class="card">
            <h3 class="card-title">Cara dapat poin</h3>
            <div class="flow-step"><div class="step-number">1</div><div><h4>Setor barang bekas</h4><p>Makin banyak, makin besar poinnya. Kalau didonasikan, dapat bonus 50%.</p></div></div>
            <div class="flow-step"><div class="step-number">2</div><div><h4>Ikut kegiatan</h4><p>Poin masuk setelah Anda tercatat hadir.</p></div></div>
            <div class="flow-step"><div class="step-number">3</div><div><h4>Jual atau beli produk tetangga</h4><p>Poin masuk setelah barang diterima.</p></div></div>
        </div>
    </div>
    <h2 class="section-title">Pilih hadiah</h2>
    <div class="grid three">
        @foreach ($rewards as $reward)
            @php($stock = $reward->availableStock())
            <div class="card item-card">
                <div class="item-art product-photo">@if ($reward->image_path)<img src="{{ \App\Support\Uploads::url($reward->image_path) }}" alt="Foto {{ $reward->name }}">@else<img src="{{ asset('images/logo.png') }}" alt="">@endif</div>
                <div><h3>{{ $reward->name }}</h3><p>{{ $reward->description }}</p></div>
                <div class="row between"><span class="point-pill">{{ number_format($reward->points_required, 0, ',', '.') }} poin</span><span class="small muted">Stok {{ $stock }}</span></div>
                @php($approved = auth()->user()->residentProfile?->status === 'active')
                @if ($approved)
                    <form method="POST" action="{{ route('citizen.rewards.redeem') }}" data-confirm="Tukar {{ $reward->points_required }} poin dengan {{ $reward->name }}? Ambil hadiahnya di bank sampah RW.">
                        @csrf
                        <input type="hidden" name="reward_id" value="{{ $reward->id }}">
                        <button class="btn primary block" type="submit" @disabled($stock <= 0 || $available < $reward->points_required)>
                            {{ $available < $reward->points_required ? 'Poin belum cukup' : ($stock <= 0 ? 'Stok habis' : 'Tukar') }}
                        </button>
                    </form>
                @else
                    <button class="btn primary block" type="button" disabled>Tunggu akun disetujui</button>
                @endif
            </div>
        @endforeach
    </div>
    <h2 class="section-title">Riwayat poin</h2>
    <div class="card">
        <x-table-toolbar placeholder="Cari kegiatan atau kode" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Waktu</th><th>Dari</th><th>Kode</th><th>Poin</th></tr></thead>
                <tbody>
                    @forelse ($ledger as $row)
                        <tr>
                            <td>{{ $row->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</td>
                            <td>{{ $row->description }}</td>
                            <td>{{ $row->reference_code ?: '—' }}</td>
                            <td><strong style="color:{{ $row->points_delta < 0 ? '#8B2E1D' : 'var(--green-deep)' }}">{{ $row->points_delta > 0 ? '+' : '' }}{{ number_format($row->points_delta, 0, ',', '.') }} poin</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada poin. Mulai dari setor barang bekas atau ikut kegiatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $ledger->links() }}
    </div>
@endsection