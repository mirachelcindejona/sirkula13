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
    <div class="field full">
        <label for="material-standard">Standar penerimaan</label>
        <textarea id="material-standard" name="acceptance_standard" rows="3">{{ old('acceptance_standard', $material->acceptance_standard ?? '') }}</textarea>
    </div>
</div>
