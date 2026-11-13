@extends('layouts.app')
@section('title', 'Tambah kegiatan')
@section('heading', 'Tambah kegiatan')
@section('eyebrow', 'Pengelola kegiatan')
@section('subtitle', 'Simpan sebagai draft jika belum siap, atau tayangkan agar warga langsung bisa melihatnya.')
@section('actions')
    <a class="btn outline" href="{{ route('program.events') }}">Kembali</a>
@endsection
@section('content')
    <div class="card">
        <form method="POST" action="{{ route('program.events.store') }}" enctype="multipart/form-data">
            @csrf
            @include('pages.program._event-fields')
            <div class="form-actions">
                <a class="btn outline" href="{{ route('program.events') }}">Batal</a>
                <button class="btn outline" name="publish" value="draft" type="submit">Simpan draft</button>
                <button class="btn primary" name="publish" value="published" type="submit">Tayangkan</button>
            </div>
        </form>
    </div>
@endsection
