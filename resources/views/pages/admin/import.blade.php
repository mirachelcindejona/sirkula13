@extends('layouts.app')
@section('title', 'Persetujuan impor offline')
@section('heading', 'Persetujuan impor offline')
@section('eyebrow', 'Pratinjau sebelum posting')
@section('subtitle', 'Baris tidak valid tidak diposting. Poin dihitung ulang oleh sistem.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Berkas</th><th>Valid</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td>{{ $batch->reference_code }}</td>
                            <td>{{ $batch->file_name }}</td>
                            <td>{{ $batch->valid_rows }}/{{ $batch->total_rows }}</td>
                            <td><x-badge :status="$batch->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada impor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $batches->links() }}
    </div>
@endsection
