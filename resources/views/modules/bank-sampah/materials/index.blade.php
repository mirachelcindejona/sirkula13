@extends('layouts.app')
@section('title', 'Jenis material')
@section('heading', 'Jenis material')
@section('eyebrow', 'Admin')
@section('subtitle', 'Daftar material yang bisa diterima bank sampah. Reward tidak dibuat dari sini.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.tariff') }}">Kembali ke tarif</a>
    <a href="{{ route('admin.materials.create') }}" class="btn primary">Tambah material</a>
@endsection
@section('content')
    <form method="GET" class="card">
        <div class="form-grid">
            <div class="field">
                <label for="material-search">Cari nama atau kode</label>
                <input id="material-search" name="search" value="{{ request('search') }}" placeholder="Contoh: plastik, PLAS">
            </div>
            <div class="field">
                <label for="material-status">Status</label>
                <select id="material-status" name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <a class="btn outline" href="{{ route('admin.materials.index') }}">Reset</a>
            <button class="btn secondary" type="submit">Saring</button>
        </div>
    </form>
    <div class="card gap-top">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Nama</th><th>Satuan</th><th>Standar</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($materials as $material)
                        <tr>
                            <td><strong>{{ $material->code }}</strong></td>
                            <td>{{ $material->icon }} {{ $material->name }}</td>
                            <td>{{ $material->unitLabel() }}</td>
                            <td>{{ $material->acceptance_standard ?: '—' }}</td>
                            <td><x-badge :status="$material->is_active ? 'active' : 'inactive'" /></td>
                            <td>
                                <div class="row">
                                    <a href="{{ route('admin.materials.edit', $material) }}" class="btn small-btn outline">Ubah</a>
                                    <form method="POST" action="{{ route('admin.materials.toggle-status', $material) }}" data-confirm="{{ $material->is_active ? 'Nonaktifkan' : 'Aktifkan' }} material {{ $material->name }}?">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn small-btn {{ $material->is_active ? 'danger' : 'secondary' }}" type="submit">{{ $material->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada jenis material.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $materials->links() }}
    </div>
@endsection
