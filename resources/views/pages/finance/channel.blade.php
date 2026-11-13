@extends('layouts.app')
@section('title', 'Rekening dan QRIS')
@section('heading', 'Rekening & QRIS')
@section('eyebrow', 'Ditampilkan ke warga dan publik')
@section('subtitle', 'Nomor rekening atau QRIS ini muncul di halaman donasi, bersama kontak sponsorship.')
@section('content')
    <form method="POST" action="{{ route('finance.channel.update') }}" class="card" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div class="field">
                <label for="bank-name">Nama bank</label>
                <input id="bank-name" name="bank_name" value="{{ old('bank_name', $channel['bank_name']) }}">
            </div>
            <div class="field">
                <label for="account-number">Nomor rekening</label>
                <input id="account-number" name="account_number" value="{{ old('account_number', $channel['account_number']) }}">
            </div>
            <div class="field full">
                <label for="account-holder">Atas nama</label>
                <input id="account-holder" name="account_holder" value="{{ old('account_holder', $channel['account_holder']) }}">
            </div>
            <x-photo-field id="qris" name="qris" label="Gambar QRIS" :current="$channel['qris_path'] ? \App\Support\Uploads::url($channel['qris_path']) : null" />
            <div class="field">
                <label for="contact-name">Nama kontak donasi & sponsorship</label>
                <input id="contact-name" name="contact_name" value="{{ old('contact_name', $channel['contact_name']) }}">
            </div>
            <div class="field">
                <label for="contact-phone">Nomor yang bisa dihubungi</label>
                <input id="contact-phone" name="contact_phone" value="{{ old('contact_phone', $channel['contact_phone']) }}">
            </div>
            <div class="field full">
                <label for="sponsor-note">Catatan untuk calon sponsor</label>
                <textarea id="sponsor-note" name="sponsorship_note">{{ old('sponsorship_note', $channel['sponsorship_note']) }}</textarea>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn primary" type="submit">Simpan informasi</button>
        </div>
    </form>
@endsection
