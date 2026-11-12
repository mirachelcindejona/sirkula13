<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\EcoPointTransaction;
use App\Models\ImportBatch;
use App\Models\Material;
use App\Models\MaterialOutflow;
use App\Models\MaterialRate;
use App\Models\ResidentProfile;
use App\Models\RewardRedemption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BankOperationsController extends Controller
{
    public function dashboard(): View
    {
        return view('pages.bank.dashboard', [
            'submitted' => Deposit::query()->where('status', 'submitted')->count(),
            'verifiedToday' => Deposit::query()->whereDate('verified_at', today())->count(),
            'stockKg' => (float) Deposit::query()->where('status', 'verified')->sum('weight_kg')
                - (float) MaterialOutflow::query()->where('status', 'verified')->sum('weight_kg'),
            'handover' => RewardRedemption::query()->where('status', 'pending_pickup')->count(),
            'latest' => Deposit::query()->with(['residentProfile', 'material'])->latest()->limit(6)->get(),
        ]);
    }

    public function createDeposit(): View
    {
        return view('pages.bank.deposit-create', [
            'residents' => ResidentProfile::query()->where('status', 'active')->orderBy('full_name')->get(),
            'materials' => Material::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeDeposit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resident_profile_id' => ['nullable', 'exists:resident_profiles,id'],
            'manual_name' => ['required_without:resident_profile_id', 'nullable', 'string', 'max:150'],
            'manual_phone' => ['nullable', 'string', 'max:25'],
            'manual_email' => ['required_without:resident_profile_id', 'nullable', 'email', 'max:255', 'unique:users,email'],
            'material_id' => ['required', 'exists:materials,id'],
            'type' => ['required', 'in:sale,donation'],
            'weight_kg' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string'],
        ]);

        $profileId = $data['resident_profile_id'] ?? null;
        if (! $profileId) {
            if (empty($data['manual_name']) || empty($data['manual_email'])) {
                return back()->withInput()->withErrors([
                    'manual_name' => 'Pilih warga yang sudah ada, atau isi nama dan email warga baru.',
                ]);
            }

            $profileId = $this->createResident($data, $request->user()->id);
        }

        $rate = MaterialRate::query()
            ->where('material_id', $data['material_id'])
            ->where('status', 'approved')
            ->orderByDesc('effective_from')
            ->first();

        $weight = (float) $data['weight_kg'];
        $price = (float) ($rate->buy_price_per_kg ?? 0);
        $pointsPerKg = (float) ($rate->eco_points_per_kg ?? 0);
        $multiplier = (float) ($rate->donation_multiplier ?? 1);
        $awarded = (int) round($weight * $pointsPerKg * ($data['type'] === 'donation' ? $multiplier : 1));
        $money = $data['type'] === 'sale' ? round($weight * $price, 2) : 0;

        Deposit::query()->create([
            'reference_code' => 'SET-'.now()->format('ymdHis'),
            'resident_profile_id' => $profileId,
            'material_id' => $data['material_id'],
            'material_rate_id' => $rate?->id,
            'type' => $data['type'],
            'weight_kg' => $weight,
            'price_per_kg_snapshot' => $price,
            'price_effective_date_snapshot' => $rate?->effective_from,
            'price_official_at_creation' => $rate?->status === 'approved',
            'eco_points_per_kg_snapshot' => $pointsPerKg,
            'donation_multiplier_snapshot' => $multiplier,
            'money_amount' => $money,
            'eco_points_awarded' => $awarded,
            'accepted' => true,
            'resident_choice_confirmed' => true,
            'status' => 'submitted',
            'source' => 'online',
            'note' => $data['note'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('bank.verify')->with('success', 'Setoran berhasil dicatat dan menunggu verifikasi.');
    }

    public function verify(): View
    {
        return view('pages.bank.verify', [
            'deposits' => Deposit::query()->with(['residentProfile', 'material'])->where('status', 'submitted')->latest()->paginate(10),
        ]);
    }

    public function verifyDeposit(Request $request, Deposit $deposit): RedirectResponse
    {
        if ($deposit->status !== 'submitted') {
            return back()->with('error', 'Setoran ini sudah tidak menunggu verifikasi.');
        }

        DB::transaction(function () use ($request, $deposit): void {
            $deposit->update([
                'status' => 'verified',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            if ($deposit->resident_profile_id && $deposit->eco_points_awarded > 0) {
                EcoPointTransaction::query()->create([
                    'reference_code' => 'ECO-'.$deposit->reference_code,
                    'resident_profile_id' => $deposit->resident_profile_id,
                    'points_delta' => $deposit->eco_points_awarded,
                    'kind' => 'earn',
                    'description' => 'Setoran '.$deposit->reference_code,
                    'pointable_type' => $deposit->getMorphClass(),
                    'pointable_id' => $deposit->id,
                    'created_by' => $request->user()->id,
                    'approved_by' => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', 'Setoran '.$deposit->reference_code.' diverifikasi.');
    }

    public function inventory(): View
    {
        $materials = Material::query()->where('is_active', true)->orderBy('name')->get()->map(function (Material $material) {
            $in = (float) Deposit::query()->where('material_id', $material->id)->where('status', 'verified')->sum('weight_kg');
            $out = (float) MaterialOutflow::query()->where('material_id', $material->id)->where('status', 'verified')->sum('weight_kg');
            $material->stock_kg = $in - $out;

            return $material;
        });

        return view('pages.bank.inventory', compact('materials'));
    }

    public function residents(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $like = '%'.addcslashes($search, '%_\\').'%';
        $residents = ResidentProfile::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($like) {
                $query->where(function ($inner) use ($like) {
                    $inner->where('full_name', 'like', $like)
                        ->orWhere('resident_code', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('rt', 'like', $like)
                        ->orWhere('house_number', 'like', $like);
                });
            })
            ->orderBy('rt')
            ->orderBy('house_number')
            ->paginate(10)
            ->withQueryString();

        return view('pages.bank.residents', [
            'residents' => $residents,
            'search' => $search,
            'canRegister' => $request->user()->hasRole('superadmin'),
        ]);
    }

    public function storeResident(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('superadmin'), 403);
        $request->merge(['phone' => $request->filled('phone') ? $request->string('phone')->toString() : null]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+()\-\s]{9,25}$/', 'unique:users,phone'],
            'rt' => ['required', 'string', 'in:01,02,03,04,05,06,07,08,09'],
            'house_number' => ['required', 'string', 'max:30'],
            'household' => ['nullable', 'string', 'max:150'],
            'checked_duplicate' => ['accepted'],
        ]);

        $this->createResident([
            'manual_name' => $data['name'],
            'manual_phone' => $data['phone'] ?? null,
            'rt' => $data['rt'],
            'house_number' => $data['house_number'],
            'household' => $data['household'] ?? null,
        ], $request->user()->id);

        return back()->with('success', 'ID warga dibuat. Akun online belum diaktifkan.');
    }

    public function handover(): View
    {
        return view('pages.bank.handover', [
            'redemptions' => RewardRedemption::query()->with(['reward', 'residentProfile'])->where('status', 'pending_pickup')->latest()->paginate(10),
        ]);
    }

    public function completeHandover(Request $request, RewardRedemption $redemption): RedirectResponse
    {
        if ($redemption->status !== 'pending_pickup') {
            return back()->with('error', 'Penukaran ini sudah diproses.');
        }

        DB::transaction(function () use ($request, $redemption): void {
            $redemption->update([
                'status' => 'completed',
                'handed_by' => $request->user()->id,
                'completed_at' => now(),
            ]);

            EcoPointTransaction::query()->create([
                'reference_code' => 'RED-'.$redemption->reference_code,
                'resident_profile_id' => $redemption->resident_profile_id,
                'points_delta' => -1 * (int) $redemption->points_cost,
                'kind' => 'redeem',
                'description' => 'Penukaran '.$redemption->reference_code,
                'pointable_type' => $redemption->getMorphClass(),
                'pointable_id' => $redemption->id,
                'created_by' => $request->user()->id,
                'approved_by' => $request->user()->id,
            ]);

            $redemption->reward()->decrement('stock');
        });

        return back()->with('success', 'Reward diserahkan dan EcoPoints dipotong.');
    }

    public function import(): View
    {
        return view('pages.bank.import', [
            'batches' => ImportBatch::query()->latest()->paginate(10),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createResident(array $data, int $actorId): int
    {
        $next = ((int) ResidentProfile::query()->where('resident_code', 'like', 'W-%')->pluck('resident_code')->map(fn ($code) => (int) str_replace('W-', '', (string) $code))->max()) + 1;
        $code = 'W-'.str_pad((string) max($next, 1), 3, '0', STR_PAD_LEFT);

        $user = User::query()->create([
            'name' => $data['manual_name'],
            'email' => strtolower($code).'@warga.sirkula.local',
            'phone' => $data['manual_phone'] ?? null,
            'password' => Str::password(40),
            'user_code' => $code,
            'status' => 'active',
            'login_enabled' => false,
        ]);

        $roleId = Role::query()->where('slug', 'warga')->value('id');
        if ($roleId) {
            $user->roles()->attach($roleId, [
                'assigned_by' => $actorId,
                'reason' => 'Daftarkan warga oleh petugas.',
                'assigned_at' => now(),
            ]);
        }

        $profile = ResidentProfile::query()->create([
            'user_id' => $user->id,
            'resident_code' => $code,
            'full_name' => $data['manual_name'],
            'phone' => $data['manual_phone'] ?? null,
            'rt' => $data['rt'] ?? null,
            'house_number' => $data['house_number'] ?? null,
            'household_name' => $data['household'] ?? null,
            'display_name' => $data['manual_name'],
            'donor_display' => 'alias',
            'status' => 'active',
            'created_by' => $actorId,
            'approved_by' => $actorId,
            'approved_at' => now(),
        ]);

        return $profile->id;
    }
}
