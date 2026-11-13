<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\EcoPointTransaction;
use App\Models\Material;
use App\Models\MaterialOutflow;
use App\Models\MaterialRate;
use App\Models\Payout;
use App\Models\ResidentProfile;
use App\Models\RewardRedemption;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use App\Support\Labels;
use App\Support\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BankOperationsController extends Controller
{
    public function dashboard(): View
    {
        $stock = (float) Deposit::query()->where('status', 'verified')->where('accepted', true)->sum('weight_kg')
            - (float) MaterialOutflow::query()->where('status', 'verified')->sum('weight_kg');

        return view('pages.bank.dashboard', [
            'today' => Deposit::query()->whereDate('created_at', today())->count(),
            'stockKg' => $stock,
            'savedMoney' => (float) Payout::query()->where('status', '!=', 'paid')->sum('amount'),
            'handover' => RewardRedemption::query()->where('status', 'pending_pickup')->count(),
            'latest' => Deposit::query()->with(['residentProfile', 'material'])->latest()->limit(8)->get(),
        ]);
    }

    public function createDeposit(): View
    {
        $materials = Material::query()->where('is_active', true)->orderBy('name')->get();
        $rates = MaterialRate::query()
            ->whereDate('effective_from', '<=', today())
            ->orderByDesc('effective_from')
            ->get()
            ->unique('material_id')
            ->keyBy('material_id');

        return view('pages.bank.deposit-create', [
            'residents' => ResidentProfile::query()->where('status', 'active')->orderBy('full_name')->get(),
            'materials' => $materials,
            'rates' => $rates,
            'priceOfficial' => $rates->contains(fn (MaterialRate $rate) => $rate->status === 'approved'),
        ]);
    }

    public function storeDeposit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_type' => ['required', 'in:resident,external'],
            'resident_profile_id' => ['required_if:source_type,resident', 'nullable', 'exists:resident_profiles,id'],
            'external_name' => ['required_if:source_type,external', 'nullable', 'string', 'max:150'],
            'material_id' => ['required', 'exists:materials,id'],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'quality' => ['required', 'in:accepted,rejected'],
            'rejection_reason' => ['required_if:quality,rejected', 'nullable', 'string', 'max:500'],
            'type' => ['required', 'in:sale,donation'],
            'choice_confirmed' => ['accepted'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $rate = MaterialRate::query()
            ->where('material_id', $data['material_id'])
            ->where('status', 'approved')
            ->whereDate('effective_from', '<=', today())
            ->orderByDesc('effective_from')
            ->first();

        if ($data['type'] === 'sale' && $data['quality'] === 'accepted' && ! $rate) {
            return back()->withInput()->with('error', 'Tarif jual belum berlaku. Pilih donasi atau tetapkan tarif terlebih dahulu.');
        }

        $material = Material::query()->findOrFail($data['material_id']);
        $accepted = $data['quality'] === 'accepted';
        $weight = (float) $data['weight_kg'];
        $price = (float) ($rate->buy_price_per_kg ?? 0);
        $pointsPerKg = (float) ($rate->eco_points_per_kg ?? 0);
        $multiplier = 1.5;
        $awarded = $accepted && $data['source_type'] === 'resident'
            ? (int) round($weight * $pointsPerKg * ($data['type'] === 'donation' ? $multiplier : 1))
            : 0;
        $money = $accepted && $data['type'] === 'sale' && $data['source_type'] === 'resident'
            ? round($weight * $price, 2)
            : 0;

        DB::transaction(function () use ($request, $data, $rate, $material, $accepted, $weight, $price, $pointsPerKg, $multiplier, $awarded, $money): void {
            $deposit = Deposit::query()->create([
                'reference_code' => 'SET-'.now()->format('ymdHis').random_int(10, 99),
                'resident_profile_id' => $data['source_type'] === 'resident' ? $data['resident_profile_id'] : null,
                'external_name' => $data['source_type'] === 'external' ? $data['external_name'] : null,
                'material_id' => $data['material_id'],
                'material_rate_id' => $rate?->id,
                'type' => $data['type'],
                'weight_kg' => $weight,
                'unit_snapshot' => $material->unitLabel(),
                'price_per_kg_snapshot' => $price,
                'price_effective_date_snapshot' => $rate?->effective_from,
                'price_official_at_creation' => $rate !== null,
                'eco_points_per_kg_snapshot' => $pointsPerKg,
                'donation_multiplier_snapshot' => $multiplier,
                'money_amount' => $money,
                'eco_points_awarded' => $awarded,
                'accepted' => $accepted,
                'quality_note' => $accepted ? null : $data['rejection_reason'],
                'resident_choice_confirmed' => true,
                'status' => 'verified',
                'source' => 'counter',
                'note' => $data['note'] ?? null,
                'created_by' => $request->user()->id,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            if ($deposit->resident_profile_id && $awarded > 0) {
                EcoPointTransaction::query()->create([
                    'reference_code' => 'ECO-'.$deposit->reference_code,
                    'resident_profile_id' => $deposit->resident_profile_id,
                    'points_delta' => $awarded,
                    'kind' => 'earn',
                    'description' => 'Setoran '.$deposit->reference_code,
                    'pointable_type' => $deposit->getMorphClass(),
                    'pointable_id' => $deposit->id,
                    'created_by' => $request->user()->id,
                    'approved_by' => $request->user()->id,
                ]);
            }

            if ($deposit->resident_profile_id && $money > 0) {
                Payout::query()->create([
                    'reference_code' => 'CAIR-'.$deposit->reference_code,
                    'deposit_id' => $deposit->id,
                    'resident_profile_id' => $deposit->resident_profile_id,
                    'amount' => $money,
                    'status' => 'saved',
                    'created_by' => $request->user()->id,
                    'note' => 'Tabungan setoran. Dicairkan saat jadwal pembagian uang.',
                ]);
            }

            Audit::record('Mencatat setoran '.$deposit->reference_code, $deposit, null, [
                'asal' => $data['source_type'],
                'berat' => $weight,
                'jenis' => $data['type'],
                'uang' => $money,
                'poin' => $awarded,
                'diterima' => $accepted,
            ]);
        });

        return redirect()->route('bank.records')->with('success', 'Setoran langsung masuk ke catatan. Uang setoran jual ditabung sampai jadwal pencairan.');
    }

    public function records(): View
    {
        $deposits = Deposit::query()->with(['residentProfile', 'material'])->latest();
        Listing::search($deposits, ['reference_code', 'external_name'], ['residentProfile' => 'full_name', 'material' => 'name']);
        if ($type = Listing::oneOf('type', ['sale', 'donation'])) {
            $deposits->where('type', $type);
        }

        return view('pages.bank.records', [
            'deposits' => $deposits->listing(),
        ]);
    }

    public function inventory(): View
    {
        $materials = Material::query()->where('is_active', true)->orderBy('name')->get()->map(function (Material $material) {
            $in = (float) Deposit::query()->where('material_id', $material->id)->where('status', 'verified')->where('accepted', true)->sum('weight_kg');
            $out = (float) MaterialOutflow::query()->where('material_id', $material->id)->where('status', 'verified')->sum('weight_kg');
            $material->stock_kg = $in - $out;

            return $material;
        });

        return view('pages.bank.inventory', [
            'materials' => $materials,
            'outflows' => tap(MaterialOutflow::query()->with('material')->latest(), function ($query): void {
                Listing::search($query, ['reference_code', 'destination_name'], ['material' => 'name']);
                if ($destination = Listing::oneOf('destination', ['pengepul', 'produk', 'lainnya'])) {
                    $query->where('destination', $destination);
                }
            })->listing(),
            'totalStock' => $materials->sum('stock_kg'),
            'pendingPayment' => MaterialOutflow::query()->where('destination', 'pengepul')->where('payment_status', '!=', 'received')->count(),
        ]);
    }

    public function storeOutflow(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'material_id' => ['required', 'exists:materials,id'],
            'weight_kg' => ['required', 'numeric', 'gt:0'],
            'destination' => ['required', 'in:pengepul,produk,lainnya'],
            'destination_name' => ['required', 'string', 'max:150'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:not_received,received'],
            'proof' => ['required_if:payment_status,received', 'nullable', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $material = Material::query()->findOrFail($data['material_id']);
        $in = (float) Deposit::query()->where('material_id', $material->id)->where('status', 'verified')->where('accepted', true)->sum('weight_kg');
        $out = (float) MaterialOutflow::query()->where('material_id', $material->id)->where('status', 'verified')->sum('weight_kg');
        $available = $in - $out;
        if ((float) $data['weight_kg'] > $available + 0.0001) {
            return back()->withInput()->with('error', 'Jumlah keluar melebihi stok tersedia ('.Labels::quantity($available, $material->unitLabel()).').');
        }

        $received = $data['payment_status'] === 'received';
        $outflow = MaterialOutflow::query()->create([
            'reference_code' => 'OUT-'.now()->format('ymdHis'),
            'material_id' => $material->id,
            'weight_kg' => $data['weight_kg'],
            'destination' => $data['destination'],
            'destination_name' => $data['destination_name'],
            'amount_reported_rp' => $data['amount'] ?? 0,
            'amount_received_rp' => $received ? ($data['amount'] ?? 0) : null,
            'payment_status' => $data['payment_status'],
            'proof_reference' => $data['proof'] ?? null,
            'status' => 'verified',
            'created_by' => $request->user()->id,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'note' => $data['note'] ?? null,
        ]);

        Audit::record('Mencatat material keluar '.$outflow->reference_code, $outflow, null, [
            'material' => $material->name,
            'berat' => $data['weight_kg'],
            'tujuan' => $data['destination_name'],
        ]);

        return back()->with('success', 'Material keluar tercatat dan stok berkurang.');
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
            ->when(Listing::oneOf('status', ['active', 'inactive', 'rejected']), fn ($query, $status) => $query->where('status', $status))
            ->orderBy('rt')
            ->orderBy('house_number')
            ->listing()
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
            'redemptions' => tap(RewardRedemption::query()->with(['reward', 'residentProfile'])->where('status', 'pending_pickup')->latest(), fn ($query) => Listing::search($query, ['reference_code'], ['residentProfile' => 'full_name', 'reward' => 'name']))->listing(),
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

        Audit::record('Menyerahkan reward '.$redemption->reference_code, $redemption, ['status' => 'pending_pickup'], ['status' => 'completed']);

        return back()->with('success', 'Reward diserahkan dan EcoPoints dipotong.');
    }

    public function cancelHandover(Request $request, RewardRedemption $redemption): RedirectResponse
    {
        if ($redemption->status !== 'pending_pickup') {
            return back()->with('error', 'Penukaran ini sudah diproses.');
        }

        $redemption->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'note' => 'Dibatalkan saat serah terima. Poin dikembalikan ke saldo tersedia.',
        ]);

        return back()->with('success', 'Penukaran dibatalkan. Poin kembali bisa dipakai.');
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
