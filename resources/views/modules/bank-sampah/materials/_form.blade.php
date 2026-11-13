<div class="form-grid">
    <div class="field">
        <label for="material-code">Kode <span class="required">*</span></label>
        <input id="material-code" name="code" value="{{ old('code', $material->code ?? '') }}" required maxlength="30">
        @error('code')<div class="field-hint">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="material-short">Nama pendek</label>
        <input id="material-short" name="short_name" value="{{ old('short_name', $material->short_name ?? '') }}" maxlength="40">
    </div>
    <div class="field full">
        <label for="material-name">Nama <span class="required">*</span></label>
        <input id="material-name" name="name" value="{{ old('name', $material->name ?? '') }}" required maxlength="120">
        @error('name')<div class="field-hint">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="material-icon">Ikon</label>
        <input id="material-icon" name="icon" value="{{ old('icon', $material->icon ?? '') }}" maxlength="20">
    </div>
    <div class="field">
        <label for="material-status">Status</label>
        <select id="material-status" name="is_active">
            <option value="1" @selected(old('is_active', ($material->is_active ?? true) ? '1' : '0') == '1')>Aktif</option>
            <option value="0" @selected(old('is_active', ($material->is_active ?? true) ? '1' : '0') == '0')>Nonaktif</option>
        </select>
    </div>
    <div class="field">
        <label for="material-unit">Satuan <span class="required">*</span></label>
        <select id="material-unit" name="unit" required>
            @foreach (['kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'liter' => 'Liter', 'pcs' => 'Pcs', 'lembar' => 'Lembar', 'ikat' => 'Ikat'] as $value => $text)
                <option value="{{ $value }}" @selected(old('unit', $material->unit ?? 'kg') === $value)>{{ $text }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="material-price">Harga beli per satuan <span class="required">*</span></label>
        <input id="material-price" type="number" min="0" step="100" name="buy_price_per_kg" value="{{ old('buy_price_per_kg', isset($rate) ? $rate->buy_price_per_kg : 0) }}" required>
    </div>
    <div class="field">
        <label for="material-points">Poin jual per satuan <span class="required">*</span></label>
        <input id="material-points" type="number" min="0" step="2" name="eco_points_per_kg" value="{{ old('eco_points_per_kg', isset($rate) ? $rate->eco_points_per_kg : 0) }}" required>
        <div class="field-hint">Isi bilangan genap. Poin donasi dihitung otomatis satu setengah kali poin jual, tanpa angka di belakang koma.</div>
    </div>
    <div class="field full">
        <label for="material-standard">Standar penerimaan</label>
        <textarea id="material-standard" name="acceptance_standard" rows="3">{{ old('acceptance_standard', $material->acceptance_standard ?? '') }}</textarea>
    </div>
</div>
