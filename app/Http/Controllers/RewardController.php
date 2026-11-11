namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reward;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    public function index()
    {
        // Ambil semua reward yang stoknya masih ada
        $rewards = Reward::where('stock', '>', 0)->get();

        // Ambil data user yang sedang login (asumsi menggunakan Auth bawaan Laravel)
        $user = Auth::user();

        return view('rewards.index', compact('rewards', 'user'));
    }
}
