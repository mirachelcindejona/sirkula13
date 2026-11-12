@extends('layouts.app')
@section('title', 'Tambah material')
@section('heading', 'Tambah jenis material')
@section('eyebrow', 'Admin')
@section('subtitle', 'Jenis baru bisa dipakai setelah tarifnya ditetapkan di halaman tarif.')
@section('content')
    <form method="POST" action="{{ route('admin.materials.store') }}" class="card">
        @csrf
        @include('modules.bank-sampah.materials._form')
        <div class="form-actions">
            <a class="btn outline" href="{{ route('admin.materials.index') }}">Batal</a>
            <button class="btn primary" type="submit">Simpan material</button>
        </div>
    </form>
@endsection
