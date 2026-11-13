@extends('layouts.app')
@section('title', 'Riwayat aktivitas')
@section('heading', 'Riwayat aktivitas')
@section('eyebrow', 'Audit log')
@section('subtitle', 'Setiap perubahan penting menyimpan kondisi sebelum dan sesudahnya.')
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari aksi, peran, atau alasan" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Waktu</th><th>Aksi</th><th>Peran</th><th>Sebelum</th><th>Sesudah</th><th>Alasan</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</td>
                            <td><strong>{{ $log->action }}</strong></td>
                            <td>{{ $log->role_name ?: '—' }}</td>
                            <td>@include('pages.admin._audit-data', ['data' => $log->before_data])</td>
                            <td>@include('pages.admin._audit-data', ['data' => $log->after_data])</td>
                            <td>{{ $log->reason ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada jejak audit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
@endsection
