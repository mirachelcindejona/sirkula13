@extends('layouts.app')
@php($editing = $product?->exists)
@section('title', $editing ? 'Ubah produk' : 'Tambah produk')
@section('heading', $editing ? 'Ubah produk' : ($audience === 'warga' ? 'Tambah produk saya' : 'Tambah produk pengurus'))
@section('eyebrow', $audience === 'warga' ? 'Produk milik Anda' : 'Produk pengurus atau kelompok RW')
@section('subtitle', $audience === 'warga'
    ? 'Produk ini dijual atas nama Anda. Pembeli diarahkan ke WhatsApp, lalu Anda menandai pesanan setelah barang diterima.'
    : 'Ini untuk produk pengurus atau kelompok RW. Produk milik warga diunggah dari akun warga itu sendiri.')
@section('actions')
    <a class="btn outline" href="{{ $back }}">Kembali</a>
@endsection
@section('content')
    <div class="card">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="form-grid">
                <div class="field full">
                    <label for="product-name">Nama produk <span class="required">*</span></label>
                    <input id="product-name" name="name" required maxlength="180" value="{{ old('name', $product?->name) }}">
                </div>
                <div class="field">
                    <label for="product-price">Harga (Rp) <span class="required">*</span></label>
                    <input id="product-price" name="price" type="number" min="0" max="100000000" step="500" required value="{{ old('price', $product?->price ?? 0) }}">
                </div>
                <div class="field">
                    <label for="product-stock">Stok <span class="required">*</span></label>
                    <input id="product-stock" name="stock" type="number" min="0" max="10000" required value="{{ old('stock', $product?->stock ?? 1) }}">
                </div>
                @if ($audience === 'pengurus')
                    <div class="field">
                        <label for="product-contact-name">Nama yang dihubungi <span class="required">*</span></label>
                        <input id="product-contact-name" name="contact_name" required maxlength="150" value="{{ old('contact_name', $product?->contact_name) }}" placeholder="Misalnya Pengurus RW atau nama kelompok">
                    </div>
                @endif
                <div class="field">
                    <label for="product-phone">WhatsApp penjual <span class="required">*</span></label>
                    <input id="product-phone" name="contact_phone" type="tel" required value="{{ old('contact_phone', $product?->contact_phone ?: ($phone ?? '')) }}" placeholder="08xxxxxxxxxx">
                    <div class="field-hint">Nomor ini yang dibuka saat warga memesan.</div>
                </div>
                <x-photo-field id="product-photo" name="photo" label="Foto produk" :current="$product?->image_path ? \App\Support\Uploads::url($product->image_path) : null" />
                <div class="field full">
                    <label for="product-description">Deskripsi</label>
                    <textarea id="product-description" name="description" maxlength="2000">{{ old('description', $product?->description) }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a class="btn outline" href="{{ $back }}">Batal</a>
                <button class="btn outline" name="publish" value="draft" type="submit">Simpan draft</button>
                <button class="btn primary" name="publish" value="published" type="submit">Tayangkan</button>
            </div>
        </form>
    </div>
@endsection
