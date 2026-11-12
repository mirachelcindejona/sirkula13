@extends('layouts.app')
@section('title', 'Ubah reward')
@section('heading', 'Ubah reward')
@section('eyebrow', 'Admin')
@section('content')
    <form method="POST" action="{{ route('admin.rewards.update', $reward) }}" class="card">
        @csrf
        @method('PUT')
        @include('pages.admin.rewards._form', ['reward' => $reward])
        <div class="form-actions">
            <a class="btn outline" href="{{ route('admin.rewards.index') }}">Batal</a>
            <button class="btn primary" type="submit">Simpan perubahan</button>
        </div>
    </form>
@endsection
