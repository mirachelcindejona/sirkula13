@extends('layouts.app')
@section('title', 'Tambah reward')
@section('heading', 'Tambah reward')
@section('eyebrow', 'Admin')
@section('subtitle', 'Reward ditukar dengan EcoPoints, terpisah dari produk yang dijual warga.')
@section('content')
    <form method="POST" action="{{ route('admin.rewards.store') }}" class="card" enctype="multipart/form-data">
        @csrf
        @include('pages.admin.rewards._form')
        <div class="form-actions">
            <a class="btn outline" href="{{ route('admin.rewards.index') }}">Batal</a>
            <button class="btn primary" type="submit">Simpan reward</button>
        </div>
    </form>
@endsection
