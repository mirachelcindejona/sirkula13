@extends('layouts.app')
@section('title', 'EcoPoints & reward')
@section('heading', 'EcoPoints & reward')
@section('eyebrow', 'Poin untuk kontribusi nyata')
@section('subtitle', 'Total poin menentukan rank. Saldo tersedia digunakan untuk penukaran reward yang stoknya masih ada.')
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
                    <div class="eyebrow">Saldo dapat digunakan</div>
                    <div class="kpi-value">{{ number_format($available, 0, ',', '.') }}</div>
                    <div class="kpi-label">EcoPoints tersedia untuk reward</div>
                </div>
                <div class="rank-badge"><span class="rank-medal">{{ $rank['emoji'] }}</span><span>{{ $rank['name'] }}</span></div>
            </div>
            <div class="divider"></div>
            <div class="row between"><span class="small muted">Total poin terkumpul</span><strong>{{ number_format($total, 0, ',', '.') }} poin</strong></div>
            <div class="rank-progress"><span style="width:{{ $pct }}%"></span></div>
            <p class="small muted">{{ $next ? 'Perlu '.number_format(max(0, $next - $total), 0, ',', '.').' poin lagi menuju rank berikutnya.' : 'Anda berada di rank tertinggi.' }} Penukaran reward mengurangi saldo, tetapi tidak mengurangi total untuk rank.</p>
        </div>
        <div class="card">
            <h3 class="card-title">Bagaimana EcoPoints dihitung?</h3>
            <div class="flow-step"><div class="step-number">1</div><div><h4>Setoran material</h4><p>Berat aktual × tarif poin per kg. Donasi material mendapat bonus 50%.</p></div></div>
            <div class="flow-step"><div class="step-number">2</div><div><h4>Kegiatan</h4><p>50 poin saat hadir terverifikasi, dan +50 poin untuk tugas relawan berbeda yang disahkan.</p></div></div>
            <div class="flow-step"><div class="step-number">3</div><div><h4>Produk</h4><p>50 poin untuk penjual dan 30 poin untuk pembeli setelah transaksi selesai dan terverifikasi, sesuai ketentuan.</p></div></div>
        </div>
    </div>
    <h2 class="section-title">Reward tersedia</h2>
    <div class="grid three">
        @foreach ($rewards as $reward)
            @php($stock = $reward->availableStock())
            <div class="card item-card">
                <div class="item-art"><span>{{ $reward->icon }}</span></div>
                <div><h3>{{ $reward->name }}</h3><p>{{ $reward->description }}</p></div>
                <div class="row between"><span class="point-pill">{{ number_format($reward->points_required, 0, ',', '.') }} poin</span><span class="small muted">Stok {{ $stock }}</span></div>
                <form method="POST" action="{{ route('citizen.rewards.redeem') }}" data-confirm="Tukar {{ $reward->points_required }} poin untuk {{ $reward->name }}? Poin ditahan sampai reward diserahkan.">
                    @csrf
                    <input type="hidden" name="reward_id" value="{{ $reward->id }}">
                    <button class="btn primary block" type="submit" @disabled($stock <= 0 || $available < $reward->points_required)>
                        {{ $available < $reward->points_required ? 'Poin belum cukup' : ($stock <= 0 ? 'Stok habis' : 'Tukar reward') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
    <h2 class="section-title">Riwayat perubahan poin</h2>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Waktu</th><th>Aktivitas</th><th>Referensi</th><th>Perubahan</th></tr></thead>
                <tbody>
                    @forelse ($ledger as $row)
                        <tr>
                            <td>{{ $row->created_at?->locale('id')->translatedFormat('d M Y HH:mm') }}</td>
                            <td>{{ $row->description }}</td>
                            <td>{{ $row->reference_code ?: '—' }}</td>
                            <td><strong style="color:{{ $row->points_delta < 0 ? '#8B2E1D' : 'var(--green-deep)' }}">{{ $row->points_delta > 0 ? '+' : '' }}{{ number_format($row->points_delta, 0, ',', '.') }} poin</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada perubahan poin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $ledger->links() }}
    </div>
@endsection