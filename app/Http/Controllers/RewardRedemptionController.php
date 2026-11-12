<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RewardRedemptionController extends Controller
{
    /**
     * Memesan reward. Poin dan stok hanya direservasi sampai petugas
     * menyelesaikan serah terima.
     */
    public function redeem(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'reward_id' => 'required|exists:rewards,id',
        ]);

        $user = $request->user()->load('residentProfile');
        $profile = $user->residentProfile;
        $reward = Reward::query()->findOrFail($validated['reward_id']);

        if ($profile === null || $profile->status !== 'active') {
            return $this->fail($request, 'Hanya warga yang sudah disetujui yang dapat menukar reward.', 422);
        }

        if (! $reward->is_active || $reward->availableStock() <= 0) {
            return $this->fail($request, 'Maaf, stok reward ini sudah habis.', 400);
        }

        if ($profile->availablePoints() < $reward->points_required) {
            return $this->fail($request, 'EcoPoints kamu tidak mencukupi untuk menukar reward ini.', 400);
        }

        try {
            $redemption = DB::transaction(function () use ($profile, $reward, $request): RewardRedemption {
            $locked = Reward::query()->whereKey($reward->id)->lockForUpdate()->firstOrFail();

            if ($locked->availableStock() <= 0 || $profile->availablePoints() < $locked->points_required) {
                throw new \RuntimeException('Saldo atau stok berubah. Silakan periksa lalu coba kembali.');
            }

            return RewardRedemption::query()->create([
                'reference_code' => 'RED-'.now()->format('ymdHis').'-'.Str::upper(Str::random(4)),
                'reward_id' => $locked->id,
                'resident_profile_id' => $profile->id,
                'points_cost' => $locked->points_required,
                'status' => 'pending_pickup',
                'created_by' => $request->user()->id,
            ]);
            });
        } catch (\RuntimeException $exception) {
            return $this->fail($request, $exception->getMessage(), 409);
        }

        $profile->refresh();
        $message = 'Reward berhasil dipesan. Poin dan stok ditahan sampai barang diserahkan.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'remaining_points' => $profile->availablePoints(),
                    'reward_name' => $reward->name,
                    'redemption_id' => $redemption->id,
                    'status' => $redemption->status,
                ],
            ]);
        }

        return redirect()->route('citizen.points')->with('success', $message);
    }

    private function fail(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
