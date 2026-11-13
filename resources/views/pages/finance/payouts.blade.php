@extends('layouts.app')
@section('title', 'Pencairan setoran')
@section('heading', 'Pencairan setoran')
@section('eyebrow', 'Tabungan warga')
@section('subtitle', 'Uang setoran tidak diberikan saat menimbang. Nilai ditabung dan dicairkan saat ada jadwal pembagian.')
@section('content')
    <div class="grid three">
        <div class="card kpi"><div class="kpi-icon"><x-icon name="wallet" /></div><div class="kpi-value">{{ \App\Support\Labels::money($savedTotal) }}</div><div class="kpi-label">Masih ditabung</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="receipt" /></div><div class="kpi-value">{{ $savedCount }}</div><div class="kpi-label">Siap dicairkan</div></div>
        <div class="card kpi"><div class="kpi-icon"><x-icon name="check" /></div><div class="kpi-value">{{ $paidCount }}</div><div class="kpi-label">Sudah dicairkan</div></div>
    </div>
    <div class="callout warning gap-top"><x-icon name="alert" /><div><strong>Cairkan hanya saat uang benar-benar diberikan</strong>Masukkan jumlah yang diserahkan. Jika angkanya salah, pencairan terakhir masih bisa dibatalkan dalam 24 jam.</div></div>
    <h2 class="section-title">Daftar tabungan</h2>
    <div class="card">
        <x-table-toolbar placeholder="Cari kode atau nama warga" :filters="[['name' => 'status', 'label' => 'Semua status', 'options' => ['saved' => 'Ditabung', 'paid' => 'Sudah dicairkan']]]" />
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Warga</th><th>Nilai</th><th>Sudah dicairkan</th><th>Sisa</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($payouts as $payout)
                        <tr>
                            <td><strong>{{ $payout->reference_code }}</strong><br><span class="small muted">{{ $payout->deposit?->reference_code }}</span></td>
                            <td>{{ $payout->residentProfile?->full_name }}<br><span class="small muted">{{ $payout->residentProfile?->resident_code }}</span></td>
                            <td>{{ \App\Support\Labels::money($payout->amount) }}</td>
                            <td>{{ \App\Support\Labels::money($payout->paid_amount) }}</td>
                            <td>{{ \App\Support\Labels::money($payout->remaining()) }}</td>
                            <td><x-badge :status="$payout->displayStatus()" /></td>
                            <td>
                                @if ($payout->remaining() > 0)
                                    <button class="btn small-btn primary" type="button" data-pay-open data-action="{{ route('finance.payouts.pay', $payout) }}" data-code="{{ $payout->reference_code }}" data-name="{{ $payout->residentProfile?->full_name }}" data-remaining="{{ $payout->remaining() }}">Cairkan</button>
                                @endif
                                @if ($cash = $payout->reversibleCash())
                                    <form method="POST" action="{{ route('finance.payouts.reverse', $payout) }}" data-confirm="Batalkan pencairan {{ \App\Support\Labels::money($cash->amount) }} untuk {{ $payout->reference_code }}? Sisa tabungan bertambah kembali.">
                                        @csrf
                                        <button class="btn small-btn outline gap-top" type="submit">Batalkan {{ \App\Support\Labels::money($cash->amount) }}</button>
                                    </form>
                                @elseif ($payout->remaining() <= 0)
                                    <span class="small muted">Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada tabungan setoran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $payouts->links() }}
    </div>
    <dialog id="pay-dialog" class="confirm-dialog">
        <form method="POST" id="pay-form">
            @csrf
            <h3 id="pay-title">Cairkan tabungan</h3>
            <p class="small muted" id="pay-note"></p>
            <div class="field gap-top">
                <label for="pay-amount">Cairkan berapa <span class="required">*</span></label>
                <input id="pay-amount" name="amount" type="number" min="1" step="1" required>
                <div class="field-hint">Tidak boleh melebihi sisa. Jika salah, batalkan dalam 24 jam.</div>
            </div>
            <div class="field gap-top">
                <div class="checkline">
                    <input id="pay-confirm" type="checkbox" name="confirm" value="1" required>
                    <label for="pay-confirm">Uang sejumlah ini sudah diberikan kepada warga.</label>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn outline" type="button" id="pay-cancel">Batal</button>
                <button class="btn primary" type="submit">Simpan pencairan</button>
            </div>
        </form>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('pay-dialog');
            const form = document.getElementById('pay-form');
            const amount = document.getElementById('pay-amount');
            document.querySelectorAll('[data-pay-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    const remaining = Number(button.dataset.remaining);
                    form.action = button.dataset.action;
                    document.getElementById('pay-title').textContent = 'Cairkan ' + button.dataset.code;
                    document.getElementById('pay-note').textContent = (button.dataset.name || 'Warga') + ' · sisa ' + remaining.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
                    amount.max = remaining;
                    amount.value = remaining;
                    document.getElementById('pay-confirm').checked = false;
                    dialog.showModal();
                });
            });
            document.getElementById('pay-cancel').addEventListener('click', () => dialog.close());
        })();
    </script>
@endsection
