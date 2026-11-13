<?php

namespace App\Http\Controllers;

use App\Exceptions\UserMessage;
use App\Models\AppSetting;
use App\Models\CashTransaction;
use App\Models\Deposit;
use App\Models\Donation;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Payout;
use App\Models\ResidentProfile;
use App\Support\Audit;
use App\Support\CashCategories;
use App\Support\Labels;
use App\Support\Listing;
use App\Support\Notices;
use App\Support\Uploads;
use App\Support\SimpleWorkbook;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function dashboard(): View
    {
        $in = (float) CashTransaction::query()->where('direction', 'in')->sum('amount');
        $out = (float) CashTransaction::query()->where('direction', 'out')->sum('amount');

        return view('pages.finance.dashboard', [
            'balance' => $in - $out,
            'due' => (float) Payout::query()->where('status', '!=', 'paid')->sum(DB::raw('GREATEST(amount - paid_amount, 0)')),
            'awaitingDonations' => (float) Donation::query()->where('status', 'pending_receipt')->sum('amount'),
        ]);
    }

    public function payouts(): View
    {
        $rows = Payout::query()->with(['residentProfile', 'deposit', 'cashOuts'])->latest();
        Listing::search($rows, ['reference_code'], ['residentProfile' => 'full_name']);
        if ($status = Listing::oneOf('status', ['saved', 'paid', 'awaiting_approval', 'approved'])) {
            $rows->where('status', $status);
        }
        $rows = $rows->listing();

        return view('pages.finance.payouts', [
            'payouts' => $rows,
            'savedTotal' => (float) Payout::query()->where('status', '!=', 'paid')->sum(DB::raw('GREATEST(amount - paid_amount, 0)')),
            'savedCount' => Payout::query()->where('status', 'saved')->count(),
            'paidCount' => Payout::query()->where('status', 'paid')->count(),
        ]);
    }

    public function pay(Request $request, Payout $payout): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'confirm' => ['accepted'],
        ]);

        try {
            $message = DB::transaction(function () use ($request, $payout, $data): string {
                $locked = Payout::query()->whereKey($payout->id)->lockForUpdate()->firstOrFail();
                if ($locked->status === 'paid' || $locked->remaining() <= 0) {
                    throw new UserMessage('Pencairan ini sudah dibayarkan.');
                }

                $amount = round((float) $data['amount'], 2);
                $remaining = $locked->remaining();
                if ($amount > $remaining) {
                    throw new UserMessage('Jumlah melebihi sisa tabungan '.Labels::money($remaining).'.');
                }

                $before = ['status' => $locked->status, 'sudah_dicairkan' => (float) $locked->paid_amount];
                $paid = round((float) $locked->paid_amount + $amount, 2);
                $settled = $paid >= round((float) $locked->amount, 2);
                $locked->forceFill([
                    'paid_amount' => $paid,
                    'status' => $settled ? 'paid' : 'saved',
                    'paid_by' => $request->user()->id,
                    'approved_by' => $locked->approved_by ?? $request->user()->id,
                    'approved_at' => $locked->approved_at ?? now(),
                    'paid_at' => now(),
                    'proof_reference' => 'Cair '.Labels::money($amount),
                ])->save();

                CashTransaction::query()->create([
                    'reference_code' => 'KAS-'.$locked->reference_code.'-'.now()->format('Hisv'),
                    'transaction_at' => now(),
                    'direction' => 'out',
                    'category' => 'Pencairan setoran warga',
                    'amount' => $amount,
                    'description' => 'Pencairan tabungan setoran '.$locked->reference_code,
                    'proof_reference' => Labels::money($amount),
                    'cashable_type' => $locked->getMorphClass(),
                    'cashable_id' => $locked->id,
                    'created_by' => $request->user()->id,
                    'posted_at' => now(),
                ]);

                Audit::record('Mencairkan '.$locked->reference_code, $locked, $before, [
                    'status' => $locked->status,
                    'jumlah' => $amount,
                    'sisa' => $locked->remaining(),
                ]);

                Notices::toProfile(
                    $locked->residentProfile,
                    'Tabungan dicairkan',
                    'Tabungan '.$locked->reference_code.' dicairkan '.Labels::money($amount).'. Sisa '.Labels::money($locked->remaining()).'.',
                    route('citizen.deposits')
                );

                return 'Uang '.Labels::money($amount).' untuk '.$locked->reference_code.' dicatat. Sisa '.Labels::money($locked->remaining()).'.';
            });
        } catch (UserMessage $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', $message);
    }

    public function reversePayout(Request $request, Payout $payout): RedirectResponse
    {
        try {
            $message = DB::transaction(function () use ($request, $payout): string {
                $locked = Payout::query()->whereKey($payout->id)->lockForUpdate()->firstOrFail();
                $cash = $locked->cashOuts()->lockForUpdate()->first();
                if (! $cash || ! $cash->canRevise()) {
                    throw new UserMessage('Pencairan terakhir hanya bisa dibatalkan dalam 24 jam.');
                }

                $amount = round((float) $cash->amount, 2);
                $cash->forceFill(['reversed_at' => now()])->save();
                $paid = max(0, round((float) $locked->paid_amount - $amount, 2));
                $locked->forceFill([
                    'paid_amount' => $paid,
                    'status' => $paid >= round((float) $locked->amount, 2) && $paid > 0 ? 'paid' : 'saved',
                    'paid_at' => $paid > 0 ? $locked->paid_at : null,
                ])->save();

                CashTransaction::query()->create([
                    'reference_code' => 'KAS-REV-'.$cash->id.'-'.now()->format('His'),
                    'transaction_at' => now(),
                    'direction' => 'in',
                    'category' => 'Koreksi pencairan',
                    'amount' => $amount,
                    'description' => 'Pembatalan pencairan '.$locked->reference_code,
                    'cashable_type' => $locked->getMorphClass(),
                    'cashable_id' => $locked->id,
                    'created_by' => $request->user()->id,
                    'posted_at' => now(),
                ]);

                Audit::record('Membatalkan pencairan '.$locked->reference_code, $locked, ['jumlah' => $amount], [
                    'sudah_dicairkan' => $paid,
                    'sisa' => $locked->remaining(),
                ], 'Koreksi dalam 24 jam');

                Notices::toProfile(
                    $locked->residentProfile,
                    'Pencairan dibatalkan',
                    'Pencairan '.$locked->reference_code.' sebesar '.Labels::money($amount).' dibatalkan. Sisa tabungan '.Labels::money($locked->remaining()).'.',
                    route('citizen.deposits')
                );

                return 'Pencairan '.Labels::money($amount).' dibatalkan. Sisa tabungan kembali '.Labels::money($locked->remaining()).'.';
            });
        } catch (UserMessage $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', $message);
    }

    public function cashbook(): View
    {
        return view('pages.finance.cashbook', [
            'rows' => tap(CashTransaction::query()->orderByDesc('transaction_at'), function ($query): void {
                Listing::search($query, ['reference_code', 'category', 'description']);
                if ($direction = Listing::oneOf('direction', ['in', 'out'])) {
                    $query->where('direction', $direction);
                }
            })->listing(),
            'in' => (float) CashTransaction::query()->where('direction', 'in')->sum('amount'),
            'out' => (float) CashTransaction::query()->where('direction', 'out')->sum('amount'),
        ]);
    }

    public function createCash(): View
    {
        return view('pages.finance.cash-form', ['entry' => null]);
    }

    public function storeCash(Request $request): RedirectResponse
    {
        $entry = new CashTransaction;
        $this->fillCash($entry, $request);
        $entry->reference_code = 'KAS-'.now()->format('ymdHis');
        $entry->created_by = $request->user()->id;
        $entry->posted_at = now();
        $entry->save();
        Audit::record('Menambah buku kas '.$entry->reference_code, $entry, null, $entry->only(['direction', 'category', 'amount', 'description']));

        return redirect()->route('finance.cashbook')->with('success', 'Catatan kas langsung masuk buku.');
    }

    public function editCash(CashTransaction $cash): View
    {
        abort_unless($cash->canRevise(), 403, 'Catatan yang sudah lewat satu hari tidak dapat diubah.');

        return view('pages.finance.cash-form', ['entry' => $cash]);
    }

    public function updateCash(Request $request, CashTransaction $cash): RedirectResponse
    {
        abort_unless($cash->canRevise(), 403);
        $before = $cash->only(['direction', 'category', 'amount', 'description', 'proof_reference']);
        $this->fillCash($cash, $request);
        $cash->save();
        Audit::record('Mengubah buku kas '.$cash->reference_code, $cash, $before, $cash->only(['direction', 'category', 'amount', 'description', 'proof_reference']));

        return redirect()->route('finance.cashbook')->with('success', 'Catatan kas diperbarui.');
    }

    public function destroyCash(CashTransaction $cash): RedirectResponse
    {
        abort_unless($cash->canRevise(), 403);
        $before = $cash->only(['reference_code', 'direction', 'amount', 'description']);
        $code = $cash->reference_code;
        $cash->delete();
        Audit::record('Menghapus buku kas '.$code, null, $before, null, 'Koreksi dalam 24 jam');

        return back()->with('success', 'Catatan kas dihapus.');
    }

    public function donations(): View
    {
        return view('pages.finance.donations', [
            'pending' => tap(Donation::query()->where('status', 'pending_receipt')->latest(), fn ($query) => Listing::search($query, ['reference_code', 'donor_name_snapshot', 'purpose']))->get(),
            'donations' => tap(Donation::query()->latest(), function ($query): void {
                Listing::search($query, ['reference_code', 'donor_name_snapshot', 'purpose']);
                if ($status = Listing::oneOf('status', ['pending_receipt', 'verified'])) {
                    $query->where('status', $status);
                }
            })->listing(),
        ]);
    }

    public function verifyDonation(Request $request, Donation $donation): RedirectResponse
    {
        if ($donation->status === 'verified') {
            return back()->with('error', 'Donasi ini sudah diverifikasi.');
        }

        $data = $request->validate([
            'payment_channel' => ['required', 'in:cash,transfer'],
            'proof' => array_merge(['required_if:payment_channel,transfer'], array_slice(Uploads::rules(), 1)),
            'confirm' => ['accepted'],
        ], Uploads::messages('proof'));

        $proof = $donation->proof_reference;
        if ($request->hasFile('proof')) {
            $proof = Uploads::store($request->file('proof'), 'donations');
        }

        $points = (int) floor(((float) $donation->amount) / 1000);
        $before = ['status' => $donation->status];
        $donation->forceFill([
            'status' => 'verified',
            'payment_channel' => $data['payment_channel'],
            'proof_reference' => $proof,
            'verified_by' => $request->user()->id,
            'received_at' => now(),
            'support_points_awarded' => $points,
        ])->save();

        CashTransaction::query()->create([
            'reference_code' => 'KAS-'.$donation->reference_code,
            'transaction_at' => now(),
            'direction' => 'in',
            'category' => 'Donasi uang',
            'amount' => $donation->amount,
            'description' => 'Donasi '.$donation->reference_code.' · '.($data['payment_channel'] === 'transfer' ? 'transfer' : 'tunai'),
            'proof_reference' => $proof,
            'cashable_type' => $donation->getMorphClass(),
            'cashable_id' => $donation->id,
            'created_by' => $request->user()->id,
            'posted_at' => now(),
        ]);

        Audit::record('Memverifikasi donasi '.$donation->reference_code, $donation, $before, [
            'status' => 'verified',
            'saluran' => $data['payment_channel'],
            'poin_dukungan' => $points,
        ]);

        return back()->with('success', 'Donasi diterima, masuk kas, dan SupportPoints dicatat.');
    }

    public function channel(): View
    {
        return view('pages.finance.channel', [
            'channel' => AppSetting::donationChannel(),
        ]);
    }

    public function updateChannel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'sponsorship_note' => ['nullable', 'string', 'max:1000'],
            'qris' => Uploads::rules(),
        ], Uploads::messages('qris'));

        $current = AppSetting::donationChannel();
        if ($request->hasFile('qris')) {
            $current['qris_path'] = Uploads::store($request->file('qris'), 'donations');
        }

        foreach (['bank_name', 'account_number', 'account_holder', 'contact_name', 'contact_phone', 'sponsorship_note'] as $key) {
            $current[$key] = $data[$key] ?? '';
        }

        $setting = AppSetting::query()->firstOrNew(['key' => 'donation_channel']);
        $before = $setting->exists ? $setting->value : null;
        $setting->forceFill([
            'group' => 'donation',
            'value' => $current,
            'updated_by' => $request->user()->id,
        ])->save();
        Audit::record('Memperbarui rekening donasi', $setting, is_array($before) ? $before : null, $current);

        return back()->with('success', 'Informasi donasi dan sponsorship diperbarui.');
    }

    public function reports(): View
    {
        return view('pages.finance.reports', $this->reportData());
    }

    public function exportReports(): StreamedResponse
    {
        $data = $this->reportData();
        $rows = [
            ['Warga aktif', $data['activeResidents']],
            ['Setoran tercatat', $data['deposits']],
            ['Berat jual (kg)', $data['soldKg']],
            ['Berat donasi (kg)', $data['donatedKg']],
            ['Material keluar (kg)', $data['outgoingKg']],
            ['Kas masuk', $data['in']],
            ['Kas keluar', $data['out']],
            ['Saldo kas', $data['balance']],
            ['Tabungan belum cair', $data['due']],
            ['Donasi belum diverifikasi', $data['awaitingDonations']],
        ];
        foreach ($data['events'] as $event) {
            $rows[] = ['Hadir: '.$event->title, $event->attendances_count];
        }

        return SimpleWorkbook::download('laporan-sirkula-'.now()->format('Ymd').'.xlsx', ['Indikator', 'Nilai'], $rows);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(): array
    {
        $in = (float) CashTransaction::query()->where('direction', 'in')->sum('amount');
        $out = (float) CashTransaction::query()->where('direction', 'out')->sum('amount');

        return [
            'activeResidents' => ResidentProfile::query()->where('status', 'active')->count(),
            'deposits' => Deposit::query()->where('status', 'verified')->count(),
            'soldKg' => (float) Deposit::query()->where('status', 'verified')->where('accepted', true)->where('type', 'sale')->sum('weight_kg'),
            'donatedKg' => (float) Deposit::query()->where('status', 'verified')->where('accepted', true)->where('type', 'donation')->sum('weight_kg'),
            'outgoingKg' => (float) \App\Models\MaterialOutflow::query()->where('status', 'verified')->sum('weight_kg'),
            'in' => $in,
            'out' => $out,
            'balance' => $in - $out,
            'due' => (float) Payout::query()->where('status', '!=', 'paid')->sum(DB::raw('GREATEST(amount - paid_amount, 0)')),
            'awaitingDonations' => (float) Donation::query()->where('status', 'pending_receipt')->sum('amount'),
            'events' => Event::query()->where('status', 'published')->withCount(['attendances' => fn ($query) => $query->where('status', 'attended')])->orderByDesc('event_date')->limit(8)->get(),
        ];
    }

    private function fillCash(CashTransaction $entry, Request $request): void
    {
        $allowed = CashCategories::for($request->input('direction'));
        if ($entry->exists && $request->input('category') === $entry->category) {
            $allowed[] = $entry->category;
        }

        $data = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'category' => ['required', 'string', 'max:100', Rule::in(array_values(array_unique($allowed)))],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:1000'],
            'proof_reference' => ['required', 'string', 'max:180'],
        ], [
            'category.in' => 'Pilih kategori yang sesuai dengan jenis pemasukan atau pengeluaran.',
        ]);

        $entry->forceFill([
            'direction' => $data['direction'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'transaction_at' => $data['transaction_date'].' 12:00:00',
            'description' => $data['description'],
            'proof_reference' => $data['proof_reference'],
            'posted_at' => $entry->posted_at ?? now(),
        ]);
    }
}
