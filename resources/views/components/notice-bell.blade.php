@auth
    @php($unread = \App\Models\Notice::query()->where('user_id', auth()->id())->whereNull('read_at')->count())
    <div class="notice-wrap" id="notice-root" data-poll="{{ route('notices.poll') }}" data-read-all="{{ route('notices.read-all') }}" data-login="{{ route('login') }}">
        <button class="icon-button notice-button" id="notice-bell" type="button" aria-expanded="false" aria-controls="notice-panel" aria-label="Pemberitahuan">
            <x-icon name="bell" />
            <span class="notice-count" @if ($unread < 1) hidden @endif>{{ $unread > 9 ? '9+' : $unread }}</span>
        </button>
        <div class="notice-panel" id="notice-panel" hidden>
            <div class="notice-head">
                <strong>Pemberitahuan</strong>
                <form method="POST" action="{{ route('notices.read-all') }}">
                    @csrf
                    <button class="text-link" type="submit">Tandai sudah dibaca</button>
                </form>
            </div>
            <div id="notice-list" class="notice-list">
                <p class="small muted">Memuat pemberitahuan.</p>
            </div>
            <div class="notice-foot">
                <button class="btn small-btn outline" id="notice-browser" type="button">Nyalakan notifikasi browser</button>
                <a class="text-link" href="{{ route('notices.index') }}">Lihat semua</a>
            </div>
        </div>
    </div>
@endauth
