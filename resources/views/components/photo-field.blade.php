@props([
    'name',
    'id',
    'label' => 'Foto',
    'required' => false,
    'current' => null,
])
<div class="field full">
    <label for="{{ $id }}">{{ $label }} @if ($required)<span class="required">*</span>@endif</label>
    <label class="upload-box {{ $current ? 'has-preview' : '' }}" for="{{ $id }}">
        <input id="{{ $id }}" name="{{ $name }}" type="file" accept="image/jpeg,image/png,image/webp" @required($required)>
        <img src="{{ $current }}" alt="" @unless ($current) hidden @endunless>
        <span class="upload-box-icon"><x-icon name="upload" /></span>
        <strong>Pilih atau jatuhkan foto</strong>
        <span class="upload-box-hint">JPG, PNG, atau WEBP. Maksimal 2 MB.</span>
    </label>
    @error($name)<div class="field-hint">{{ $message }}</div>@enderror
</div>
