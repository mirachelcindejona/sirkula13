<?php

namespace App\Http\Controllers\Residents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residents\StoreManualResidentRequest;
use App\Http\Requests\Residents\UpdateResidentRequest;
use App\Models\ResidentProfile;
use App\Models\User;
use App\Services\Residents\ResidentAccess;
use App\Services\Residents\ResidentAudit;
use App\Services\Residents\ResidentRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResidentController extends Controller
{
    private function guard(Request $request): void
    {
        ResidentAccess::require($request->user(), 'superadmin', 'operator-bank-sampah');
    }

    public function index(Request $request)
    {
        $this->guard($request);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rt' => ['nullable', 'regex:/^0[1-9]$/'],
            'status' => ['nullable', 'in:active,pending,needs_info,rejected'],
            'access' => ['nullable', 'in:manual,online'],
        ]);
        $query = ResidentProfile::query()->with('user');
        if ($q = trim($filters['q'] ?? '')) {
            $escaped = addcslashes($q, '%_\\');
            $query->where(fn ($builder) => $builder
                ->where('full_name', 'like', '%'.$escaped.'%')
                ->orWhere('resident_code', 'like', '%'.$escaped.'%')
                ->orWhere('phone', 'like', '%'.$escaped.'%')
                ->orWhere('house_number', 'like', '%'.$escaped.'%')
                ->orWhere('household_name', 'like', '%'.$escaped.'%')
            );
        }
        if (! empty($filters['rt'])) {
            $query->where('rt', $filters['rt']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (($filters['access'] ?? null) === 'manual') {
            $query->whereNull('user_id');
        } elseif (($filters['access'] ?? null) === 'online') {
            $query->whereNotNull('user_id');
        }

        return view('modules.residents.index', [
            'profiles' => $query->orderBy('rt')->orderBy('house_number')->orderBy('full_name')->paginate(15)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        $this->guard($request);

        return view('modules.residents.create');
    }

    public function store(StoreManualResidentRequest $request, ResidentRegistrationService $registration)
    {
        $this->guard($request);
        $data = $request->validated();
        $phone = ResidentRegistrationService::normalizePhone($data['phone'] ?? null);
        $name = trim($data['full_name']);
        $house = trim($data['house_number']);
        $sameName = ResidentProfile::query()
            ->whereRaw('LOWER(full_name) = ?', [mb_strtolower($name)])
            ->where('rt', $data['rt'])->where('house_number', $house)->exists();
        if ($sameName) {
            throw ValidationException::withMessages(['full_name' => 'Profil dengan nama, RT, dan nomor rumah ini sudah ada. Periksa daftar warga.']);
        }
        if ($phone && (
            ResidentProfile::query()->whereIn('phone', $this->phoneVariants($phone))->exists() ||
            User::query()->whereIn('phone', $this->phoneVariants($phone))->exists()
        )) {
            throw ValidationException::withMessages(['phone' => 'Nomor sudah terdaftar. Cari profil yang sudah ada.']);
        }

        $profile = DB::transaction(function () use ($data, $phone, $name, $house, $request, $registration): ResidentProfile {
            $profile = ResidentProfile::query()->create([
                'full_name' => $name,
                'phone' => $phone,
                'rt' => $data['rt'],
                'house_number' => $house,
                'household_name' => $data['household_name'] ?? null,
                'display_name' => ($data['display_name'] ?? null) ?: $name,
                'leaderboard_visible' => $data['leaderboard_visible'],
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);
            $registration->issueResidentCode($profile);
            ResidentAudit::record($request->user(), 'resident_manual_created', ResidentProfile::class, $profile->id, null, ['resident_code' => $profile->resident_code, 'status' => 'active'], 'Duplikasi diperiksa operator');

            return $profile;
        });

        return redirect()->route('residents.show', $profile)->with('success', 'Profil manual dibuat. ID '.$profile->resident_code.' dapat dipakai untuk pencatatan.');
    }

    public function show(Request $request, ResidentProfile $resident)
    {
        $this->guard($request);
        $resident->load('user', 'creator', 'approver');

        return view('modules.residents.show', ['profile' => $resident]);
    }

    public function edit(Request $request, ResidentProfile $resident)
    {
        $this->guard($request);
        $this->guardEdit($request, $resident);

        return view('modules.residents.edit', ['profile' => $resident]);
    }

    public function update(UpdateResidentRequest $request, ResidentProfile $resident)
    {
        $this->guard($request);
        $this->guardEdit($request, $resident);
        $data = $request->validated();
        $phone = ResidentRegistrationService::normalizePhone($data['phone'] ?? null);
        if ($resident->user_id && $phone !== ResidentRegistrationService::normalizePhone($resident->phone)) {
            throw ValidationException::withMessages(['phone' => 'Nomor akun online harus diubah melalui proses verifikasi A1.']);
        }
        if ($resident->user_id && trim($data['full_name']) !== $resident->full_name) {
            throw ValidationException::withMessages(['full_name' => 'Nama akun online harus diubah melalui proses sinkronisasi A1.']);
        }
        if ($phone && ResidentProfile::query()->where('id', '!=', $resident->id)->whereIn('phone', $this->phoneVariants($phone))->exists()) {
            throw ValidationException::withMessages(['phone' => 'Nomor kontak sudah digunakan profil lain.']);
        }
        if ($phone && User::query()->where('id', '!=', $resident->user_id)->whereIn('phone', $this->phoneVariants($phone))->exists()) {
            throw ValidationException::withMessages(['phone' => 'Nomor kontak sudah digunakan akun lain.']);
        }
        DB::transaction(function () use ($request, $resident, $data, $phone): void {
            $before = $resident->only(['rt', 'house_number', 'display_name', 'leaderboard_visible']);
            $resident->update([
                'full_name' => trim($data['full_name']),
                'phone' => $phone,
                'rt' => $data['rt'],
                'house_number' => trim($data['house_number']),
                'household_name' => $data['household_name'] ?? null,
                'display_name' => ($data['display_name'] ?? null) ?: trim($data['full_name']),
                'leaderboard_visible' => $data['leaderboard_visible'],
            ]);
            ResidentAudit::record($request->user(), 'resident_profile_updated', ResidentProfile::class, $resident->id, $before, $resident->only(['rt', 'house_number', 'display_name', 'leaderboard_visible']));
        });

        return redirect()->route('residents.show', $resident)->with('success', 'Data profil diperbarui.');
    }

    private function guardEdit(Request $request, ResidentProfile $resident): void
    {
        if ($resident->user_id) {
            ResidentAccess::require($request->user(), 'superadmin');
        }
    }

    private function phoneVariants(string $phone): array
    {
        return [$phone, '62'.substr($phone, 1), '+62'.substr($phone, 1)];
    }
}
