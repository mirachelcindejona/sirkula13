@extends('layouts.app')
@section('title', 'Catat setoran')
@section('heading', 'Catat setoran')
@section('eyebrow', 'Operator')
@section('actions')
    <a class="btn outline" href="{{ route('bank.dashboard') }}">Batal</a>
@endsection
@section('content')
    <form method="POST" action="{{ route('bank.deposits.store') }}" class="card grid gap-4 p-5 md:grid-cols-2" data-confirm="Simpan setoran ini untuk diverifikasi?">
        @csrf
        <label class="block md:col-span-2">
            <span class="field-label">Warga yang sudah ada</span>
            <select name="resident_profile_id" class="field-input">
                <option value="">Warga baru</option>
                @foreach ($residents as $resident)
                    <option value="{{ $resident->id }}" @selected(old('resident_profile_id') == $resident->id)>{{ $resident->resident_code }} — {{ $resident->full_name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block"><span class="field-label">Nama warga baru</span><input name="manual_name" value="{{ old('manual_name') }}" class="field-input"></label>
        <label class="block"><span class="field-label">Email warga baru</span><input type="email" name="manual_email" value="{{ old('manual_email') }}" class="field-input"></label>
        <label class="block"><span class="field-label">HP warga baru</span><input name="manual_phone" value="{{ old('manual_phone') }}" class="field-input"></label>
        <label class="block">
            <span class="field-label">Material</span>
            <select name="material_id" class="field-input" required>
                @foreach ($materials as $material)
                    <option value="{{ $material->id }}" @selected(old('material_id') == $material->id)>{{ $material->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="field-label">Pilihan warga</span>
            <select name="type" class="field-input">
                <option value="sale">Jual</option>
                <option value="donation">Donasi sampah</option>
            </select>
        </label>
        <label class="block"><span class="field-label">Berat (kg)</span><input type="number" step="0.001" min="0.001" name="weight_kg" value="{{ old('weight_kg') }}" class="field-input" required></label>
        <label class="block md:col-span-2"><span class="field-label">Catatan</span><textarea name="note" class="field-input" rows="3">{{ old('note') }}</textarea></label>
        @if ($errors->any())
            <div class="md:col-span-2 text-sm font-semibold text-danger">{{ $errors->first() }}</div>
        @endif
        <div class="md:col-span-2"><button class="btn primary">Simpan setoran</button></div>
    </form>
@endsection
