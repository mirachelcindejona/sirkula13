<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\EcoPointTransaction;
use App\Models\ProductOrder;
use App\Models\Reward;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CitizenController extends Controller
{
    public function profile(): View
    {
        $profile = auth()->user()->residentProfile;

        return view('pages.citizen.profile', [
            'user' => auth()->user(),
            'profile' => $profile,
            'rank' => $profile?->rank(),
        ]);
    }

    public function deposits(): View
    {
        $profile = auth()->user()->residentProfile;
        $base = $profile
            ? Deposit::query()->with(['material', 'payout'])->where('resident_profile_id', $profile->id)
            : null;
        $deposits = $base ? (clone $base)->latest()->paginate(10) : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        $earned = $base ? (int) (clone $base)->where('status', 'verified')->sum('eco_points_awarded') : 0;
        $amountDue = $base
            ? (float) \App\Models\Payout::query()->where('resident_profile_id', $profile->id)->where('status', '!=', 'paid')->sum('amount')
            : 0;

        return view('pages.citizen.deposits', compact('deposits', 'earned', 'amountDue'));
    }

    public function points(): View
    {
        $profile = auth()->user()->residentProfile;
        $ledger = $profile
            ? EcoPointTransaction::query()->where('resident_profile_id', $profile->id)->latest()->paginate(10, ['*'], 'riwayat')
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        $rewards = Reward::query()->where('is_active', true)->orderBy('points_required')->get();

        return view('pages.citizen.points', [
            'profile' => $profile,
            'ledger' => $ledger,
            'rewards' => $rewards,
            'redemptions' => $profile
                ? $profile->rewardRedemptions()->with('reward')->latest()->get()
                : collect(),
        ]);
    }

    public function education(): View
    {
        $materials = DB::table('education_materials')
            ->where('status', 'published')
            ->orderBy('title')
            ->get();

        return view('pages.citizen.education', compact('materials'));
    }

    public function orders(): View
    {
        $profile = auth()->user()->residentProfile;
        $orders = $profile
            ? ProductOrder::query()->with(['product', 'seller'])->where('buyer_profile_id', $profile->id)->latest()->paginate(10)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        return view('pages.citizen.orders', compact('orders'));
    }
}
