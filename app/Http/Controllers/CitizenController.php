<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\EcoPointTransaction;
use App\Models\ProductOrder;
use App\Models\Reward;
use App\Support\Listing;
use Illuminate\Pagination\LengthAwarePaginator;
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
        if ($base) {
            Listing::search($base, ['reference_code'], ['material' => 'name']);
            if ($type = Listing::oneOf('type', ['sale', 'donation'])) {
                $base->where('type', $type);
            }
        }
        $deposits = $base ? (clone $base)->latest()->listing() : new LengthAwarePaginator([], 0, Listing::perPage(), 1, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);
        $earned = $base ? (int) (clone $base)->where('accepted', true)->sum('eco_points_awarded') : 0;
        $amountDue = $base
            ? (float) \App\Models\Payout::query()->where('resident_profile_id', $profile->id)->where('status', '!=', 'paid')->sum(DB::raw('GREATEST(amount - paid_amount, 0)'))
            : 0;

        return view('pages.citizen.deposits', compact('deposits', 'earned', 'amountDue'));
    }

    public function points(): View
    {
        $profile = auth()->user()->residentProfile;
        $ledger = $profile
            ? tap(EcoPointTransaction::query()->where('resident_profile_id', $profile->id)->latest(), fn ($query) => Listing::search($query, ['description', 'reference_code']))->listing('riwayat')
            : new LengthAwarePaginator([], 0, Listing::perPage(), 1, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'riwayat',
                'query' => request()->query(),
            ]);
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

    public function orders(): View
    {
        $profile = auth()->user()->residentProfile;
        $orders = $profile
            ? tap(ProductOrder::query()->with(['product', 'seller'])->where('buyer_profile_id', $profile->id)->latest(), fn ($query) => Listing::search($query, ['reference_code'], ['product' => 'name']))->listing()
            : new LengthAwarePaginator([], 0, Listing::perPage(), 1, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]);

        return view('pages.citizen.orders', compact('orders'));
    }
}
