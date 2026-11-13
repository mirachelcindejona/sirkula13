<div class="grid two">
    <div class="card">
        <h3 class="card-title">Transfer atau pindai QRIS</h3>
        <p class="card-subtitle">Gunakan rekening atau QRIS resmi RW 13. Cantumkan nama Anda pada berita transfer.</p>
        @if ($channel['bank_name'] || $channel['account_number'])
            <div class="list-row"><div class="list-icon"><x-icon name="wallet" /></div><div class="grow"><h4>{{ $channel['bank_name'] ?: 'Rekening' }} · {{ $channel['account_number'] ?: 'Nomor menyusul' }}</h4><p>a.n. {{ $channel['account_holder'] ?: 'Pengurus RW 13' }}</p></div></div>
        @else
            <p class="small muted gap-top">Nomor rekening belum diumumkan.</p>
        @endif
        @if ($channel['qris_path'])
            <img src="{{ \App\Support\Uploads::url($channel['qris_path']) }}" alt="QRIS donasi SIRKULA RW 13" class="gap-top" style="width:min(220px,100%);border-radius:16px">
        @endif
    </div>
    <div class="card">
        <h3 class="card-title">Donasi dan sponsorship terbuka</h3>
        <p>Program ini menerima donasi masyarakat dan dukungan sponsor untuk kegiatan lingkungan RW 13.</p>
        <div class="list-row"><div class="list-icon"><x-icon name="user" /></div><div class="grow"><h4>{{ $channel['contact_name'] ?: 'Pengurus SIRKULA' }}</h4><p>{{ $channel['contact_phone'] ?: 'Nomor kontak menyusul' }}</p></div></div>
        @if ($channel['sponsorship_note'])
            <p class="small">{{ $channel['sponsorship_note'] }}</p>
        @endif
    </div>
</div>
