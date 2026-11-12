@extends('layouts.app')
@section('title', 'Buku kas')
@section('heading', 'Buku kas')
@section('eyebrow', 'Bendahara')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Arah</th><th>Kategori</th><th>Nominal</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="font-bold">{{ $row->reference_code }}</td>
                        <td>{{ $row->direction === 'in' ? 'Masuk' : 'Keluar' }}</td>
                        <td>{{ $row->category }}</td>
                        <td>{{ \App\Support\Labels::money($row->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada mutasi kas.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $rows->links() }}
    </div>
    <h2 class="section-title mt-8">Pengeluaran</h2>
    <div class="card mt-3 overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Kategori</th><th>Nominal</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($expenses as $expense)
                    <tr>
                        <td>{{ $expense->reference_code }}</td>
                        <td>{{ $expense->category }}</td>
                        <td>{{ \App\Support\Labels::money($expense->amount) }}</td>
                        <td><x-badge :status="$expense->status" /></td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        {{ $expenses->links() }}
    </div>
@endsection
