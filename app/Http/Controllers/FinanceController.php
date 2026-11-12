<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Donation;
use App\Models\Expense;
use App\Models\Payout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function dashboard(): View
    {
        $in = (float) CashTransaction::query()->where('direction', 'in')->sum('amount');
        $out = (float) CashTransaction::query()->where('direction', 'out')->sum('amount');

        return view('pages.finance.dashboard', [
            'balance' => $in - $out,
            'in' => $in,
            'out' => $out,
            'waiting' => Payout::query()->where('status', 'awaiting_approval')->count(),
        ]);
    }

    public function payouts(): View
    {
        return view('pages.finance.payouts', [
            'payouts' => Payout::query()->latest()->paginate(10),
        ]);
    }

    public function pay(Request $request, Payout $payout): RedirectResponse
    {
        if ($payout->status === 'paid') {
            return back()->with('error', 'Pencairan ini sudah dibayar.');
        }

        $payout->forceFill([
            'status' => 'paid',
            'paid_by' => $request->user()->id,
            'approved_by' => $payout->approved_by ?? $request->user()->id,
            'approved_at' => $payout->approved_at ?? now(),
            'paid_at' => now(),
        ])->save();

        return back()->with('success', 'Pencairan '.$payout->reference_code.' ditandai sudah dibayar.');
    }

    public function cashbook(): View
    {
        return view('pages.finance.cashbook', [
            'rows' => CashTransaction::query()->orderByDesc('transaction_at')->paginate(12),
            'expenses' => Expense::query()->latest()->paginate(10, ['*'], 'pengeluaran'),
        ]);
    }

    public function donations(): View
    {
        return view('pages.finance.donations', [
            'donations' => Donation::query()->latest()->paginate(10),
        ]);
    }

    public function verifyDonation(Request $request, Donation $donation): RedirectResponse
    {
        if ($donation->status === 'verified') {
            return back()->with('error', 'Donasi ini sudah diverifikasi.');
        }

        $points = (int) floor(((float) $donation->amount) / 1000);
        $donation->forceFill([
            'status' => 'verified',
            'verified_by' => $request->user()->id,
            'received_at' => now(),
            'support_points_awarded' => $points,
        ])->save();

        return back()->with('success', 'Donasi diverifikasi. SupportPoints dicatat terpisah dari EcoPoints.');
    }

    public function reports(): View
    {
        return view('pages.finance.reports', [
            'donations' => (float) Donation::query()->where('status', 'verified')->sum('amount'),
            'payouts' => (float) Payout::query()->where('status', 'paid')->sum('amount'),
            'expenses' => (float) Expense::query()->where('status', 'paid')->sum('amount'),
        ]);
    }
}
