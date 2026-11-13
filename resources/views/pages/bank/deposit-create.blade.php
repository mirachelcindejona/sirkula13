@extends('layouts.app')
@section('title', 'Catat setoran')
@section('heading', 'Catat setoran')
@section('eyebrow', 'Operator bank sampah')
@section('subtitle', 'Setoran langsung masuk catatan. Uang jual ditabung dan dicairkan saat jadwal pembagian.')
@section('actions')
    <a class="btn outline" href="{{ route('bank.records') }}">Lihat catatan</a>
@endsection
@section('content')
    @php
        $rateMap = $materials->mapWithKeys(fn ($material) => [$material->id => [
            'price' => (float) ($rates->get($material->id)->buy_price_per_kg ?? 0),
            'points' => (float) ($rates->get($material->id)->eco_points_per_kg ?? 0),
            'unit' => $material->unitLabel(),
        ]]);
    @endphp
    <div class="grid two">
        <div class="card">
            <div class="card-head"><div><h3 class="card-title">1. Data setoran</h3><p class="card-subtitle">Pilih warga RW 13 atau tulis nama jika setoran datang dari luar.</p></div></div>
            <form method="POST" action="{{ route('bank.deposits.store') }}" id="deposit-form" data-confirm="Simpan setoran ini ke catatan?">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="deposit-source">Asal setoran <span class="required">*</span></label>
                        <select id="deposit-source" name="source_type">
                            <option value="resident" @selected(old('source_type', 'resident') === 'resident')>Warga RW 13</option>
                            <option value="external" @selected(old('source_type') === 'external')>Dari luar RW</option>
                        </select>
                    </div>
                    <div class="field" id="deposit-resident-field">
                        <label for="deposit-resident">ID / nama warga <span class="required">*</span></label>
                        <select id="deposit-resident" name="resident_profile_id">
                            <option value="">Pilih warga</option>
                            @foreach ($residents as $resident)
                                <option value="{{ $resident->id }}" @selected((string) old('resident_profile_id') === (string) $resident->id)>{{ $resident->resident_code }} · {{ $resident->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field full" id="deposit-external-field" hidden>
                        <label for="deposit-external-name">Nama yang menyetor <span class="required">*</span></label>
                        <input id="deposit-external-name" name="external_name" value="{{ old('external_name') }}" placeholder="Nama atau alias">
                    </div>
                    <div class="field">
                        <label for="deposit-material">Jenis material <span class="required">*</span></label>
                        <select id="deposit-material" name="material_id" required>
                            @foreach ($materials as $material)
                                <option value="{{ $material->id }}" @selected((string) old('material_id') === (string) $material->id)>{{ $material->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="deposit-weight">Jumlah diterima (<span id="deposit-unit">kg</span>) <span class="required">*</span></label>
                        <input id="deposit-weight" name="weight_kg" type="number" min="0.01" max="1000" step="0.01" value="{{ old('weight_kg', '1') }}" required>
                    </div>
                    <div class="field">
                        <label for="deposit-quality">Hasil pemeriksaan <span class="required">*</span></label>
                        <select id="deposit-quality" name="quality">
                            <option value="accepted" @selected(old('quality', 'accepted') === 'accepted')>Diterima</option>
                            <option value="rejected" @selected(old('quality') === 'rejected')>Ditolak</option>
                        </select>
                    </div>
                    <div class="field" id="deposit-rejection-field" hidden>
                        <label for="deposit-rejection">Alasan penolakan <span class="required">*</span></label>
                        <input id="deposit-rejection" name="rejection_reason" value="{{ old('rejection_reason') }}" placeholder="Jelaskan dengan bahasa sederhana">
                    </div>
                    <div class="field full">
                        <label>Tujuan material <span class="required">*</span></label>
                        <div class="choice-grid">
                            <label class="radio-card {{ $priceOfficial ? '' : 'choice-disabled' }}">
                                <input type="radio" name="type" value="sale" @checked(old('type', $priceOfficial ? 'sale' : 'donation') === 'sale') @disabled(! $priceOfficial)>
                                <span><strong>Jual</strong><p>Nilai uang ditabung dan EcoPoints jual masuk sekarang.</p></span>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="type" value="donation" @checked(old('type', $priceOfficial ? 'sale' : 'donation') === 'donation')>
                                <span><strong>Donasi</strong><p>Tidak ada uang. Poin = poin jual + poin jual × 0,5.</p></span>
                            </label>
                        </div>
                    </div>
                    <div class="field full">
                        <div class="checkline">
                            <input type="checkbox" id="resident-choice" name="choice_confirmed" value="1" @checked(old('choice_confirmed')) required>
                            <label for="resident-choice">Pilihan jual atau donasi sudah dijelaskan dan dikonfirmasi.</label>
                        </div>
                    </div>
                    <div class="field full">
                        <label for="deposit-note">Catatan petugas</label>
                        <textarea id="deposit-note" name="note" placeholder="Opsional">{{ old('note') }}</textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <a class="btn outline" href="{{ route('bank.dashboard') }}">Batal</a>
                    <button class="btn primary" type="submit">Simpan ke catatan</button>
                </div>
            </form>
        </div>
        <div class="stack">
            <div class="card">
                <div class="card-head"><div><h3 class="card-title">2. Ringkasan</h3><p class="card-subtitle">Angka mengikuti jenis, berat, dan pilihan.</p></div></div>
                <div id="deposit-preview"></div>
            </div>
            <div class="callout {{ $priceOfficial ? 'success' : 'warning' }}">
                <x-icon name="{{ $priceOfficial ? 'check' : 'alert' }}" />
                <div><strong>{{ $priceOfficial ? 'Tarif sudah bisa dipakai' : 'Tarif jual belum berlaku' }}</strong>{{ $priceOfficial ? 'Setoran jual memakai harga yang sedang berlaku.' : 'Pilih donasi sampai tarif ditetapkan.' }}</div>
            </div>
            <div class="card">
                <h3 class="card-title">Aturan cepat</h3>
                <div class="timeline gap-top">
                    <div class="timeline-item"><h4>Jumlah harus aktual</h4><p>Tidak menggunakan perkiraan dari warga. Satuan mengikuti jenis material.</p></div>
                    <div class="timeline-item"><h4>Material ditolak = tidak berpoin</h4><p>Catat alasan agar warga tahu apa yang perlu diperbaiki.</p></div>
                    <div class="timeline-item"><h4>Donasi tidak dibayar uang</h4><p>Bonus poin tidak boleh ditafsirkan sebagai nominal rupiah.</p></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (() => {
            const rates = @json($rateMap);
            const names = @json($materials->mapWithKeys(fn ($material) => [$material->id => $material->short_name ?: $material->name]));
            const source = document.getElementById('deposit-source');
            const residentField = document.getElementById('deposit-resident-field');
            const externalField = document.getElementById('deposit-external-field');
            const resident = document.getElementById('deposit-resident');
            const external = document.getElementById('deposit-external-name');
            const quality = document.getElementById('deposit-quality');
            const rejection = document.getElementById('deposit-rejection-field');
            const preview = document.getElementById('deposit-preview');
            const money = (value) => 'Rp' + Math.round(value).toLocaleString('id-ID');
            const rejectionInput = document.getElementById('deposit-rejection');
            const unitLabel = document.getElementById('deposit-unit');
            const syncSource = () => {
                const externalMode = source.value === 'external';
                residentField.hidden = externalMode;
                externalField.hidden = !externalMode;
                resident.required = !externalMode;
                resident.disabled = externalMode;
                external.required = externalMode;
                external.disabled = !externalMode;
                if (externalMode) resident.value = '';
                resident.dispatchEvent(new Event('change', { bubbles: true }));
            };
            const syncQuality = () => {
                const rejected = quality.value === 'rejected';
                rejection.hidden = !rejected;
                rejectionInput.required = rejected;
                rejectionInput.disabled = !rejected;
                if (!rejected) rejectionInput.value = '';
            };
            const render = () => {
                const materialId = document.getElementById('deposit-material').value;
                const weight = Math.max(0, Number(document.getElementById('deposit-weight').value) || 0);
                const type = document.querySelector('input[name="type"]:checked')?.value || 'donation';
                const rate = rates[materialId] || { price: 0, points: 0, unit: 'kg' };
                unitLabel.textContent = rate.unit || 'kg';
                const rejected = quality.value === 'rejected';
                const points = rejected || source.value === 'external' ? 0 : Math.round(weight * rate.points * (type === 'donation' ? 1.5 : 1));
                const rupiah = rejected || type !== 'sale' || source.value === 'external' ? 0 : weight * rate.price;
                preview.innerHTML = `<div class="row between"><span class="small muted">Material</span><strong>${names[materialId] || '—'}</strong></div>
                    <div class="row between gap-top"><span class="small muted">Jumlah</span><strong>${weight.toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${rate.unit || 'kg'}</strong></div>
                    <div class="row between gap-top"><span class="small muted">Pilihan</span><strong>${type === 'donation' ? 'Donasi' : 'Jual'}</strong></div>
                    <div class="divider"></div>
                    <div class="row between"><span class="small muted">Nilai yang ditabung</span><strong>${money(rupiah)}</strong></div>
                    <div class="row between gap-top"><span class="small muted">EcoPoints</span><strong style="font-size:22px;color:var(--green-deep)">${points.toLocaleString('id-ID')} poin</strong></div>
                    <p class="tiny muted gap-top">${rejected ? 'Material ditolak tidak mendapat poin atau uang.' : (type === 'donation' ? 'Poin donasi = jumlah × (poin jual + poin jual × 0,5).' : 'Uang tidak diberikan sekarang. Nilai masuk tabungan warga.')}</p>`;
            };
            [source, quality, document.getElementById('deposit-material'), document.getElementById('deposit-weight')].forEach((el) => el.addEventListener('input', () => { syncSource(); syncQuality(); render(); }));
            document.querySelectorAll('input[name="type"]').forEach((el) => el.addEventListener('change', render));
            syncSource();
            syncQuality();
            render();
        })();
    </script>
@endsection
