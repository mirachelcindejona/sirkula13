@extends('layouts.app')
@section('title', 'Pemberitahuan')
@section('heading', 'Pemberitahuan')
@section('eyebrow', 'Kabar untuk akun ini')
@section('subtitle', 'Persetujuan akun, pesanan, dan pencairan muncul di sini.')
@section('actions')
    <form method="POST" action="{{ route('notices.read-all') }}">
        @csrf
        <button class="btn outline" type="submit">Tandai sudah dibaca</button>
    </form>
@endsection
@section('content')
    <div class="card">
        <div class="notice-page">
            @forelse ($notices as $notice)
                <a class="notice-row {{ $notice->read_at ? '' : 'is-unread' }}" href="{{ route('notices.read', $notice) }}">
                    <strong>{{ $notice->title }}</strong>
                    <p>{{ $notice->body }}</p>
                    <span>{{ $notice->created_at?->locale('id')->diffForHumans() }}</span>
                </a>
            @empty
                <p class="small muted">Belum ada pemberitahuan.</p>
            @endforelse
        </div>
        {{ $notices->links() }}
    </div>
@endsection
