@extends('layouts.app')
@section('title', 'Impor setoran')
@section('heading', 'Impor setoran')
@section('eyebrow', 'Operator')
@section('content')
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Berkas</th><th>Baris</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($batches as $batch)
                    <tr>
                        <td class="font-bold">{{ $batch->reference_code }}</td>
                        <td>{{ $batch->file_name }}</td>
                        <td>{{ $batch->valid_rows }}/{{ $batch->total_rows }}</td>
                        <td><x-badge :status="$batch->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada berkas impor.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $batches->links() }}
    </div>
@endsection
