<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $profile = $user?->residentProfile;
        $availablePoints = $profile?->availablePoints() ?? 0;

        $rewards = Reward::query()
            ->where('is_active', true)
            ->orderBy('points_required')
            ->get();

        return view('pages.Reward.index', compact('rewards', 'user', 'availablePoints'));
    }
}
