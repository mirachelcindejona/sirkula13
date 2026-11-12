@extends('layouts.app')
@section('title', 'Riwayat aktivitas')
@section('heading', 'Riwayat aktivitas')
@section('eyebrow', 'Jejak yang dapat diperiksa')
@section('subtitle', 'Perubahan penting tercatat bersama waktu, tindakan, dan alasan.')
@section('actions')
    <a class="btn outline" href="{{ route('admin.dashboard') }}">Kembali</a>
@endsection
@section('content')
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Waktu</th><th>Aksi</th><th>Peran</th><th>Alasan</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->locale('id')->translatedFormat('d M Y HH:mm') }}</td>
                            <td><strong>{{ $log->action }}</strong></td>
                            <td>{{ $log->role_name }}</td>
                            <td>{{ $log->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada jejak audit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
@endsection
