namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PointTransaction;
use App\Models\Rank;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    /**
     * Menambahkan EcoPoints dan XP ke warga berdasarkan aksi tertentu.
     */
    public function addPoints(Request $request, $userId)
    {
        // Validasi input dari form/request
        $request->validate([
            'points' => 'required|integer|min:1',
            'xp' => 'required|integer|min:0',
            'description' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($userId);

        // Gunakan Database Transaction agar aman dari data korup jika terjadi error
        DB::beginTransaction();
        try {
            // 1. Catat transaksi ke tabel point_transactions
            PointTransaction::create([
                'user_id' => $user->id,
                'points' => $request->points,
                'xp' => $request->xp,
                'description' => $request->description,
            ]);

            // 2. Perbarui total points dan xp di tabel users
            $user->points += $request->points;
            $user->xp += $request->xp;

            // 3. (Opsional) Cek dan perbarui Rank warga otomatis
            $this->checkAndUpdateRank($user);

            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'EcoPoints dan XP berhasil ditambahkan!',
                'data' => [
                    'current_points' => $user->points,
                    'current_xp' => $user->xp,
                    'current_rank' => $user->rank ? $user->rank->name : 'Unranked',
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan poin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper private method untuk mengecek kenaikan rank berdasarkan XP.
     */
    private function checkAndUpdateRank(User $user)
    {
        // Cari rank tertinggi yang syarat minimum XP-nya terpenuhi oleh user
        $eligibleRank = Rank::where('min_xp', '<=', $user->xp)
                            ->orderBy('min_xp', 'desc')
                            ->first();

        if ($eligibleRank && $user->rank_id !== $eligibleRank->id) {
            $user->rank_id = $eligibleRank->id;
        }
    }
}
