<?php

namespace App\Http\Controllers\Residents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residents\LinkResidentRequest;
use App\Http\Requests\Residents\ReviewResidentRequest;
use App\Models\ResidentProfile;
use App\Services\Residents\ResidentAccess;
use App\Services\Residents\ResidentRegistrationService;
use Illuminate\Http\Request;

class ResidentApprovalController extends Controller
{
    private function guard(Request $request): void
    {
        ResidentAccess::require($request->user(), 'superadmin');
    }

    public function index(Request $request)
    {
        $this->guard($request);

        return view('modules.residents.approvals', [
            'pending' => ResidentProfile::query()->with('user')
                ->whereHas('user', fn ($q) => $q->whereIn('status', ['pending', 'needs_info']))
                ->orderBy('created_at')->get(),
            'manualProfiles' => ResidentProfile::query()->whereNull('user_id')->whereNotNull('resident_code')
                ->where('status', 'active')->orderBy('rt')->orderBy('full_name')->get(),
        ]);
    }

    public function review(ReviewResidentRequest $request, ResidentProfile $resident, ResidentRegistrationService $registration)
    {
        $this->guard($request);
        $data = $request->validated();
        if ($data['decision'] === 'approved') {
            $registration->approve($resident, $request->user(), $data['reason'] ?? null);
        } else {
            $registration->review($resident, $request->user(), $data['decision'], $data['reason']);
        }

        return redirect()->route('residents.approvals.index')->with('success', 'Keputusan pendaftaran berhasil dicatat.');
    }

    public function link(LinkResidentRequest $request, ResidentProfile $resident, ResidentRegistrationService $registration)
    {
        $this->guard($request);
        $data = $request->validated();
        $target = ResidentProfile::query()->findOrFail($data['target_profile_id']);
        $registration->linkExisting($resident, $target, $request->user(), $data['reason']);

        return redirect()->route('residents.approvals.index')->with('success', 'Profil lama berhasil ditautkan. Akun masih menunggu persetujuan.');
    }
}
