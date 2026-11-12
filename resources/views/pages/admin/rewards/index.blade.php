@extends('layouts.app')
@section('title', 'Katalog reward')
@section('heading', 'Katalog reward')
@section('eyebrow', 'Admin')
@section('subtitle', 'Hadiah ini dikelola admin dan tidak diambil dari produk yang dijual warga.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.tariff') }}">Kembali ke tarif</a>
    <a href="{{ route('admin.rewards.create') }}" class="btn primary">Tambah reward</a>
@endsection
@section('content')
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Nama</th><th>Poin</th><th>Stok</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($rewards as $reward)
                        <tr>
                            <td><strong>{{ $reward->code }}</strong></td>
                            <td>{{ $reward->icon }} {{ $reward->name }}</td>
                            <td>{{ number_format($reward->points_required, 0, ',', '.') }}</td>
                            <td>{{ $reward->stock }}</td>
                            <td><x-badge :status="$reward->is_active ? 'active' : 'inactive'" /></td>
                            <td><a href="{{ route('admin.rewards.edit', $reward) }}" class="btn small-btn outline">Ubah</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada reward.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rewards->links() }}
    </div>
@endsection
