@extends('layouts.app')
@section('title', 'Tambah produk')
@section('heading', 'Tambah produk')
@section('eyebrow', 'Katalog warga')
@section('subtitle', 'Tayangkan agar langsung muncul untuk warga, atau simpan sebagai draft.')
@section('actions')
    <a class="btn outline" href="{{ route('program.catalog') }}">Kembali</a>
@endsection
@section('content')
    <div class="card">
        <form method="POST" action="{{ route('program.catalog.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="field full">
                    <label for="product-name">Nama produk <span class="required">*</span></label>
                    <input id="product-name" name="name" required value="{{ old('name') }}">
                </div>
                <div class="field">
                    <label for="product-price">Harga (Rp) <span class="required">*</span></label>
                    <input id="product-price" name="price" type="number" min="0" step="500" required value="{{ old('price', 0) }}">
                </div>
                <div class="field">
                    <label for="product-stock">Stok <span class="required">*</span></label>
                    <input id="product-stock" name="stock" type="number" min="0" required value="{{ old('stock', 1) }}">
                </div>
                <div class="field">
                    <label for="product-seller">Penjual</label>
                    <select id="product-seller" name="seller_profile_id">
                        <option value="">Kelompok RW</option>
                        @foreach ($sellers as $seller)
                            <option value="{{ $seller->id }}" @selected((string) old('seller_profile_id') === (string) $seller->id)>{{ $seller->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-photo-field id="product-photo" name="photo" label="Foto produk" />
                <div class="field full">
                    <label for="product-description">Deskripsi</label>
                    <textarea id="product-description" name="description">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a class="btn outline" href="{{ route('program.catalog') }}">Batal</a>
                <button class="btn outline" name="publish" value="draft" type="submit">Simpan draft</button>
                <button class="btn primary" name="publish" value="published" type="submit">Tayangkan</button>
            </div>
        </form>
    </div>
@endsection
