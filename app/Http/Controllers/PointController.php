<?php

namespace App\Http\Controllers;

use App\Models\EcoPointTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PointController extends Controller
{
    /**
     * Mencatat penyesuaian EcoPoints pada buku poin warga.
     * Rank tidak disimpan; rank dihitung dari total poin positif.
     */
    public function addPoints(Request $request, $userId): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|not_in:0',
            'description' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = User::query()->with('residentProfile')->findOrFail($userId);
        $profile = $user->residentProfile;

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini belum memiliki profil warga.',
            ], 422);
        }

        $points = (int) $validated['points'];

        DB::transaction(function () use ($profile, $points, $validated, $request): void {
            EcoPointTransaction::query()->create([
                'reference_code' => 'PT-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'resident_profile_id' => $profile->id,
                'points_delta' => $points,
                'kind' => $points < 0 ? 'adjustment' : 'earn',
                'description' => $validated['description'],
                'created_by' => $request->user()?->id,
                'reason' => $validated['reason'] ?? 'Penyesuaian EcoPoints',
            ]);
        });

        $profile->refresh();
        $rank = $profile->rank();

        return response()->json([
            'success' => true,
            'message' => 'EcoPoints berhasil dicatat.',
            'data' => [
                'available_points' => $profile->availablePoints(),
                'balance' => $profile->pointBalance(),
                'earned_points' => $profile->earnedPoints(),
                'rank' => $rank['name'],
            ],
        ]);
    }
}
