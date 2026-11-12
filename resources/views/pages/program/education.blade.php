@extends('layouts.app')
@section('title', 'Edukasi')
@section('heading', 'Materi edukasi')
@section('eyebrow', 'Program')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Judul</th><th>Kategori</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($materials as $material)
                    <tr>
                        <td>{{ $material->code }}</td>
                        <td class="font-bold">{{ $material->title }}</td>
                        <td>{{ $material->category }}</td>
                        <td><x-badge :status="$material->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada materi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
