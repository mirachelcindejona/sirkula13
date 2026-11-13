@extends('layouts.app')
@section('title', 'Stok dan penyaluran')
@section('heading', 'Stok & penyaluran')
@section('eyebrow', 'Material tersedia')
@section('subtitle', 'Stok dihitung dari setoran yang diterima, dikurangi material yang sudah keluar.')
@section('content')
    @php($units = $materials->map(fn ($material) => $material->unitLabel())->unique())
    @php($peak = max(0.001, (float) $materials->max('stock_kg')))
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="box" /></div><div class="kpi-value">{{ $units->count() === 1 ? \App\Support\Labels::quantity($totalStock, $units->first()) : $materials->where('stock_kg', '>', 0)->count() }}</div><div class="kpi-label">{{ $units->count() === 1 ? 'Total stok' : 'Jenis berstok' }}</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="upload" /></div><div class="kpi-value">{{ $outflows->total() }}</div><div class="kpi-label">Catatan keluar</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ $pendingPayment }}</div><div class="kpi-label">Pengepul belum lunas</div></div>
    </div>
    <div class="grid two gap-top">
        <div class="card">
            <h3 class="card-title">Stok per jenis</h3>
            @foreach ($materials as $material)
                @php($share = max(0, (float) $material->stock_kg) / $peak * 100)
                <div class="material-row">
                    <div class="material-icon">{{ $material->icon ?: '♻' }}</div>
                    <div>
                        <div class="material-title">{{ $material->short_name ?: $material->name }}</div>
                        <div class="progress-line gap-top"><span style="width:{{ number_format($share, 2, '.', '') }}%"></span></div>
                    </div>
                    <div class="text-right"><strong>{{ \App\Support\Labels::quantity($material->stock_kg, $material->unitLabel()) }}</strong></div>
                </div>
            @endforeach
        </div>
        <div class="card">
            <h3 class="card-title">Catat material keluar</h3>
            <p class="card-subtitle">Nilai rupiah baru menjadi kas setelah uang diterima dan dicatat bendahara.</p>
            <form method="POST" action="{{ route('bank.inventory.store') }}" class="gap-top">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="out-material">Jenis material <span class="required">*</span></label>
                        <select id="out-material" name="material_id" required>
                            @foreach ($materials as $material)
                                <option value="{{ $material->id }}" data-unit="{{ $material->unitLabel() }}">{{ $material->name }} · {{ \App\Support\Labels::quantity($material->stock_kg, $material->unitLabel()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="out-weight">Jumlah keluar (<span id="out-unit">kg</span>) <span class="required">*</span></label>
                        <input id="out-weight" name="weight_kg" type="number" min="0.01" step="0.01" required>
                    </div>
                    <div class="field">
                        <label for="out-destination">Tujuan <span class="required">*</span></label>
                        <select id="out-destination" name="destination">
                            <option value="pengepul">Dijual ke pengepul</option>
                            <option value="produk">Dipakai untuk produk</option>
                            <option value="lainnya">Pemanfaatan lain</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="out-name">Nama mitra <span class="required">*</span></label>
                        <input id="out-name" name="destination_name" required placeholder="Nama pengepul atau kelompok">
                    </div>
                    <div class="field">
                        <label for="out-amount">Nilai penerimaan (Rp)</label>
                        <input id="out-amount" name="amount" type="number" min="0" step="100" value="0">
                    </div>
                    <div class="field">
                        <label for="out-payment">Status uang</label>
                        <select id="out-payment" name="payment_status">
                            <option value="not_received">Belum diterima</option>
                            <option value="received">Sudah diterima</option>
                        </select>
                    </div>
                    <div class="field full">
                        <label for="out-proof">Referensi bukti</label>
                        <input id="out-proof" name="proof" placeholder="Wajib jika uang sudah diterima">
                    </div>
                    <div class="field full">
                        <label for="out-note">Catatan</label>
                        <textarea id="out-note" name="note"></textarea>
                    </div>
                </div>
                <div class="form-actions"><button class="btn primary" type="submit">Catat material keluar</button></div>
            </form>
        </div>
    </div>
    <h2 class="section-title">Riwayat material keluar</h2>
    <div class="card">
        <x-table-toolbar placeholder="Cari kode, tujuan, atau material" :filters="[['name' => 'destination', 'label' => 'Semua tujuan', 'options' => ['pengepul' => 'Pengepul', 'produk' => 'Produk', 'lainnya' => 'Lainnya']]]" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Material</th><th>Berat</th><th>Tujuan</th><th>Nilai</th><th>Kas</th><th>Bukti</th></tr></thead>
                <tbody>
                    @forelse ($outflows as $outflow)
                        <tr>
                            <td><strong>{{ $outflow->reference_code }}</strong><br><span class="small muted">{{ $outflow->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</span></td>
                            <td>{{ $outflow->material?->name }}</td>
                            <td>{{ \App\Support\Labels::quantity($outflow->weight_kg, $outflow->material?->unit) }}</td>
                            <td>{{ ucfirst($outflow->destination) }}<br>{{ $outflow->destination_name }}</td>
                            <td>{{ \App\Support\Labels::money($outflow->amount_reported_rp) }}</td>
                            <td><x-badge :status="$outflow->payment_status === 'received' ? 'paid' : 'awaiting_payment'" /></td>
                            <td>{{ $outflow->proof_reference ?: 'Belum ada' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada material keluar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $outflows->links() }}
    </div>
    <script>
        (() => {
            const material = document.getElementById('out-material');
            const unit = document.getElementById('out-unit');
            const sync = () => { unit.textContent = material.selectedOptions[0]?.dataset.unit || 'kg'; };
            material?.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
