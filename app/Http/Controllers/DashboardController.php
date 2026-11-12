<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CashTransaction;
use App\Models\Deposit;
use App\Models\Donation;
use App\Models\Event;
use App\Models\Payout;
use App\Models\Product;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(): View
    {
        $user = auth()->user();
        $profile = $user->residentProfile;
        $role = $user->primaryRoleSlug();

        return view('pages.app.home', [
            'role' => $role,
            'roleName' => $user->primaryRoleName(),
            'profile' => $profile,
            'announcements' => Announcement::query()->latest()->limit(3)->get(),
            'deposits' => $profile
                ? Deposit::query()->with('material')->where('resident_profile_id', $profile->id)->latest()->limit(4)->get()
                : collect(),
            'pendingApprovals' => User::query()->whereIn('status', ['pending', 'needs_info'])->count(),
            'pendingDeposits' => Deposit::query()->whereIn('status', ['submitted', 'needs_correction'])->count(),
            'pendingRewards' => RewardRedemption::query()->where('status', 'pending_pickup')->count(),
            'upcomingEvents' => Event::query()->where('status', 'published')->whereDate('event_date', '>=', now()->toDateString())->orderBy('event_date')->limit(3)->get(),
            'stockKg' => (float) Deposit::query()->where('status', 'verified')->where('accepted', true)->sum('weight_kg')
                - (float) DB::table('material_outflows')->whereNotIn('status', ['draft', 'rejected', 'cancelled'])->sum('weight_kg'),
            'unpaidPayouts' => Payout::query()->where('status', '!=', 'paid')->count(),
            'publishedEvents' => Event::query()->where('status', 'published')->count(),
            'pendingAttendance' => (int) DB::table('event_attendances')->where('status', 'pending_verification')->count(),
            'draftCatalog' => Product::query()->whereIn('status', ['draft', 'review'])->count(),
            'cashBalance' => (float) CashTransaction::query()->where('direction', 'in')->sum('amount')
                - (float) CashTransaction::query()->where('direction', 'out')->sum('amount'),
            'pendingDonationCount' => Donation::query()->where('status', 'pending_receipt')->count(),
            'openDecisions' => (int) DB::table('readiness_items')->where('is_active', true)->whereNotIn('id', DB::table('readiness_checks')->where('is_completed', true)->pluck('readiness_item_id'))->count(),
        ]);
    }
}
