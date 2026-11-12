<div class="form-grid">
    <div class="field">
        <label for="reward-code">Kode <span class="required">*</span></label>
        <input id="reward-code" name="code" value="{{ old('code', $reward->code ?? '') }}" required maxlength="30">
        @error('code')<div class="field-hint">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="reward-icon">Ikon</label>
        <input id="reward-icon" name="icon" value="{{ old('icon', $reward->icon ?? '🎁') }}" maxlength="30">
    </div>
    <div class="field full">
        <label for="reward-name">Nama <span class="required">*</span></label>
        <input id="reward-name" name="name" value="{{ old('name', $reward->name ?? '') }}" required maxlength="150">
        @error('name')<div class="field-hint">{{ $message }}</div>@enderror
    </div>
    <div class="field full">
        <label for="reward-description">Deskripsi</label>
        <textarea id="reward-description" name="description" rows="3">{{ old('description', $reward->description ?? '') }}</textarea>
    </div>
    <div class="field">
        <label for="reward-points">Poin yang dibutuhkan <span class="required">*</span></label>
        <input id="reward-points" type="number" min="1" name="points_required" value="{{ old('points_required', $reward->points_required ?? 100) }}" required>
    </div>
    <div class="field">
        <label for="reward-stock">Stok <span class="required">*</span></label>
        <input id="reward-stock" type="number" min="0" name="stock" value="{{ old('stock', $reward->stock ?? 0) }}" required>
    </div>
    <div class="field full">
        <div class="checkline">
            <input type="hidden" name="is_active" value="0">
            <input id="reward-active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $reward->is_active ?? true))>
            <label for="reward-active">Reward aktif dan bisa ditukar warga</label>
        </div>
    </div>
</div>
