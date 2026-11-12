namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class RewardRedemptionController extends Controller
{
    /**
     * Proses penukaran reward oleh warga.
     */
    public function redeem(Request $request)
    {
        // Validasi input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reward_id' => 'required|exists:rewards,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $reward = Reward::findOrFail($request->reward_id);

        // 1. Cek apakah stok reward masih tersedia
        if ($reward->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, stok reward ini sudah habis.'
            ], 400);
        }

        // 2. Cek apakah poin warga mencukupi
        if ($user->points < $reward->points_cost) {
            return response()->json([
                'success' => false,
                'message' => 'EcoPoints kamu tidak mencukupi untuk menukar reward ini.'
            ], 400);
        }

        // Gunakan Database Transaction untuk keamanan data
        DB::beginTransaction();
        try {
            // A. Kurangi poin warga
            $user->points -= $reward->points_cost;
            $user->save();

            // B. Catat riwayat penukaran ke tabel reward_redemptions
            $redemption = RewardRedemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'status' => 'pending', // Status awal, bisa 'completed' atau 'approved' sesuai alur aplikasi
                'redeemed_at' => now(),
            ]);

            // C. Catat pengeluaran poin ke tabel point_transactions (nilai points minus)
            PointTransaction::create([
                'user_id' => $user->id,
                'points' => -$reward->points_cost,
                'xp' => 0, // Penukaran reward tidak menambah XP
                'description' => 'Penukaran reward: ' . $reward->name,
            ]);

            // D. Kurangi stok reward
            $reward->stock -= 1;
            $reward->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reward berhasil ditukar! Silakan cek status penukaran kamu.',
                'data' => [
                    'remaining_points' => $user->points,
                    'reward_name' => $reward->name,
                    'redemption_id' => $redemption->id,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses penukaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
