@extends('layouts.app')
@section('title', 'Ubah material')
@section('heading', 'Ubah jenis material')
@section('eyebrow', 'Admin')
@section('content')
    <form method="POST" action="{{ route('admin.materials.update', $material) }}" class="card">
        @csrf
        @method('PUT')
        @include('modules.bank-sampah.materials._form', ['material' => $material])
        <div class="form-actions">
            <a class="btn outline" href="{{ route('admin.materials.index') }}">Batal</a>
            <button class="btn primary" type="submit">Simpan perubahan</button>
        </div>
    </form>
@endsection
