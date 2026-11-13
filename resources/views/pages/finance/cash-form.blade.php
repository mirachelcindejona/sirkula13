@extends('layouts.app')
@section('title', $entry ? 'Ubah catatan kas' : 'Catat kas')
@section('heading', $entry ? 'Ubah catatan kas' : 'Catat kas')
@section('eyebrow', 'Buku kas')
@section('subtitle', 'Isi hanya uang yang sudah benar-benar diterima atau dibayarkan.')
@section('actions')
    <a class="btn outline" href="{{ route('finance.cashbook') }}">Kembali</a>
@endsection
@section('content')
    <form method="POST" action="{{ $entry ? route('finance.cashbook.update', $entry) : route('finance.cashbook.store') }}" class="card">
        @csrf
        @if ($entry) @method('PUT') @endif
        <div class="form-grid">
            <div class="field">
                <label for="cash-direction">Jenis <span class="required">*</span></label>
                <select id="cash-direction" name="direction">
                    <option value="in" @selected(old('direction', $entry?->direction ?? 'in') === 'in')>Penerimaan</option>
                    <option value="out" @selected(old('direction', $entry?->direction) === 'out')>Pengeluaran</option>
                </select>
            </div>
            <div class="field">
                <label for="cash-category">Kategori <span class="required">*</span></label>
                <select id="cash-category" name="category">
                    @foreach (['Penjualan material ke pengepul', 'Penjualan produk', 'Sponsor / dukungan mitra', 'Donasi uang', 'Biaya operasional', 'Pembelian reward', 'Pencairan setoran warga', 'Pengeluaran lain'] as $category)
                        <option @selected(old('category', $entry?->category) === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="cash-amount">Nominal (Rp) <span class="required">*</span></label>
                <input id="cash-amount" name="amount" type="number" min="1" step="100" required value="{{ old('amount', $entry?->amount) }}">
            </div>
            <div class="field">
                <label for="cash-date">Tanggal <span class="required">*</span></label>
                <input id="cash-date" name="transaction_date" type="date" required value="{{ old('transaction_date', $entry?->transaction_at?->format('Y-m-d') ?? now()->toDateString()) }}">
            </div>
            <div class="field full">
                <label for="cash-description">Keterangan <span class="required">*</span></label>
                <textarea id="cash-description" name="description" required>{{ old('description', $entry?->description) }}</textarea>
            </div>
            <div class="field full">
                <label for="cash-proof">Nomor bukti <span class="required">*</span></label>
                <input id="cash-proof" name="proof_reference" required value="{{ old('proof_reference', $entry?->proof_reference) }}">
            </div>
        </div>
        <div class="form-actions">
            <a class="btn outline" href="{{ route('finance.cashbook') }}">Batal</a>
            <button class="btn primary" type="submit">Simpan catatan</button>
        </div>
    </form>
@endsection
