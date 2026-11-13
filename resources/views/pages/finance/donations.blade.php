@extends('layouts.app')
@section('title', 'Verifikasi donasi')
@section('heading', 'Verifikasi donasi')
@section('eyebrow', 'Belum diterima = belum menjadi kas')
@section('subtitle', 'Konfirmasi setelah uang benar-benar ada. Transfer wajib menyertakan bukti.')
@section('content')
    <div class="card">
        <x-table-toolbar placeholder="Cari kode, donatur, atau tujuan" :filters="[['name' => 'status', 'label' => 'Semua status', 'options' => ['pending_receipt' => 'Menunggu', 'verified' => 'Terverifikasi']]]" />
    </div>
    <div class="callout warning gap-top"><x-icon name="alert" /><div><strong>Janji donasi belum masuk kas</strong>SupportPoints baru dicatat setelah penerimaan dikonfirmasi.</div></div>
    <h2 class="section-title">Menunggu pemeriksaan</h2>
    <div class="card">
        @forelse ($pending as $donation)
            <div class="list-row">
                <div class="list-icon"><x-icon name="heart" /></div>
                <div class="grow">
                    <h4>{{ $donation->donor_name_snapshot }} · {{ \App\Support\Labels::money($donation->amount) }}</h4>
                    <p>{{ $donation->purpose }} · {{ $donation->created_at?->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</p>
                </div>
                <button class="btn small-btn primary" type="button" data-donation-open data-id="{{ $donation->id }}" data-name="{{ $donation->donor_name_snapshot }}" data-amount="{{ \App\Support\Labels::money($donation->amount) }}">Konfirmasi diterima</button>
            </div>
        @empty
            <p class="small muted">Tidak ada donasi yang menunggu.</p>
        @endforelse
    </div>
    <h2 class="section-title">Riwayat</h2>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kode</th><th>Donatur</th><th>Tujuan</th><th>Nominal</th><th>Saluran</th><th>Status</th><th>Poin</th></tr></thead>
                <tbody>
                    @forelse ($donations as $donation)
                        <tr>
                            <td><strong>{{ $donation->reference_code }}</strong></td>
                            <td>{{ $donation->donor_name_snapshot }}</td>
                            <td>{{ $donation->purpose }}</td>
                            <td>{{ \App\Support\Labels::money($donation->amount) }}</td>
                            <td>{{ $donation->payment_channel === 'transfer' ? 'Transfer' : ($donation->payment_channel === 'cash' ? 'Tunai' : '—') }}</td>
                            <td><x-badge :status="$donation->status" /></td>
                            <td>{{ $donation->status === 'verified' ? $donation->support_points_awarded.' SP' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada donasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $donations->links() }}
    </div>
    <dialog id="donation-dialog" class="confirm-dialog">
        <form method="POST" id="donation-verify-form" enctype="multipart/form-data">
            @csrf
            <h3 id="donation-dialog-title">Konfirmasi donasi</h3>
            <div class="form-grid">
                <div class="field full">
                    <label for="donation-channel">Uang diterima sebagai <span class="required">*</span></label>
                    <select id="donation-channel" name="payment_channel" required>
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div id="donation-proof-field" hidden>
                    <x-photo-field id="donation-proof" name="proof" label="Bukti transfer" />
                </div>
                <div class="field full">
                    <div class="checkline">
                        <input id="donation-confirm" type="checkbox" name="confirm" value="1" required>
                        <label for="donation-confirm">Saya memastikan uang sudah diterima dan boleh masuk kas.</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn outline" type="button" id="donation-cancel">Batal</button>
                <button class="btn primary" type="submit">Simpan penerimaan</button>
            </div>
        </form>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('donation-dialog');
            const form = document.getElementById('donation-verify-form');
            const channel = document.getElementById('donation-channel');
            const proofField = document.getElementById('donation-proof-field');
            const proof = document.getElementById('donation-proof');
            const sync = () => {
                const transfer = channel.value === 'transfer';
                proofField.hidden = !transfer;
                proof.required = transfer;
            };
            channel.addEventListener('change', sync);
            document.querySelectorAll('[data-donation-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    form.action = @json(route('finance.donations.verify', ['donation' => '__ID__'])).replace('__ID__', button.dataset.id);
                    document.getElementById('donation-dialog-title').textContent = button.dataset.name + ' · ' + button.dataset.amount;
                    form.reset();
                    sync();
                    dialog.showModal();
                });
            });
            document.getElementById('donation-cancel').addEventListener('click', () => dialog.close());
            sync();
        })();
    </script>
@endsection
