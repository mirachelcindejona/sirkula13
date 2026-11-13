@extends('layouts.app')
@section('title', 'Tarif dan aturan poin')
@section('heading', 'Tarif & aturan poin')
@section('eyebrow', 'Harga dan EcoPoints')
@section('subtitle', 'Poin jual harus bilangan genap. Poin donasi dihitung otomatis satu setengah kali poin jual, tanpa angka di belakang koma. Perubahan tidak mengubah setoran yang sudah tercatat.')
@section('actions')
    <a class="btn secondary" href="{{ route('admin.materials.create') }}">Tambah jenis material</a>
@endsection
@section('content')
    <form method="POST" action="{{ route('admin.tariff.store') }}" class="card">
        @csrf
        @if ($errors->any())
            <div class="callout warning"><x-icon name="alert" /><div><strong>Tarif belum tersimpan</strong>{{ $errors->first() }}</div></div>
        @endif
        <div class="table-wrap">
            <table>
                <thead><tr><th>Jenis material</th><th>Harga beli (Rp/kg)</th><th>Poin jual/kg</th><th>Poin donasi/kg</th></tr></thead>
                <tbody>
                    @foreach ($materials as $material)
                        @php($rate = $current->get($material->id))
                        <tr>
                            <td><strong>{{ $material->name }}</strong><br><span class="small muted">per {{ $material->unitLabel() }} · {{ $material->acceptance_standard }}</span></td>
                            <td><input type="number" min="0" step="100" name="price[{{ $material->id }}]" value="{{ old('price.'.$material->id, $rate->buy_price_per_kg ?? 0) }}" required aria-label="Harga {{ $material->name }}"></td>
                            <td><input class="sell-points" data-material="{{ $material->id }}" type="number" min="0" step="2" name="points[{{ $material->id }}]" value="{{ old('points.'.$material->id, (int) ($rate->eco_points_per_kg ?? 0)) }}" required aria-label="Poin jual {{ $material->name }}"></td>
                            <td><strong class="donate-points" data-material="{{ $material->id }}">0</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="form-grid gap-top">
            <div class="field">
                <label for="price-date">Tanggal mulai berlaku <span class="required">*</span></label>
                <input id="price-date" name="effective_from" type="date" required value="{{ old('effective_from', now()->toDateString()) }}">
            </div>
            <div class="field">
                <label for="price-status">Status <span class="required">*</span></label>
                <select id="price-status" name="official">
                    <option value="1" selected>Berlaku</option>
                    <option value="0">Simpan sebagai draft</option>
                </select>
            </div>
            <div class="field full">
                <label for="price-reason">Alasan perubahan <span class="required">*</span></label>
                <textarea id="price-reason" name="reason" required>{{ old('reason') }}</textarea>
            </div>
        </div>
        <div class="form-actions"><button class="btn primary" type="submit">Simpan tarif</button></div>
    </form>
    <h2 class="section-title">Riwayat perubahan</h2>
    <div class="card">
        <x-table-toolbar placeholder="Cari jenis material" :filters="[['name' => 'status', 'label' => 'Semua status', 'options' => ['approved' => 'Berlaku', 'draft' => 'Draft']]]" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Material</th><th>Harga</th><th>Poin jual</th><th>Poin donasi</th><th>Berlaku</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($history as $rate)
                        <tr>
                            <td>{{ $rate->material?->name }}</td>
                            <td>{{ \App\Support\Labels::money($rate->buy_price_per_kg) }}</td>
                            <td>{{ $rate->eco_points_per_kg }}</td>
                            <td>{{ number_format((int) round(((float) $rate->eco_points_per_kg) * 1.5), 0, ',', '.') }}</td>
                            <td>{{ $rate->effective_from?->format('d M Y') }}</td>
                            <td><x-badge :status="$rate->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $history->links() }}
    </div>
    <script>
        (() => {
            const paint = (input) => {
                const target = document.querySelector('.donate-points[data-material="' + input.dataset.material + '"]');
                const points = Number(input.value) || 0;
                target.textContent = points % 2 === 0
                    ? (points * 1.5).toLocaleString('id-ID', { maximumFractionDigits: 0 })
                    : 'harus genap';
            };
            document.querySelectorAll('.sell-points').forEach((input) => {
                input.addEventListener('input', () => paint(input));
                paint(input);
            });
        })();
    </script>
@endsection
