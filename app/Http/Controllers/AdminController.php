<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashTransaction;
use App\Models\Deposit;
use App\Models\Event;
use App\Models\ImportBatch;
use App\Models\MaterialRate;
use App\Models\Payout;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ReadinessItem;
use App\Models\ResidentProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $cashIn = (float) CashTransaction::query()->where('direction', 'in')->sum('amount');
        $cashOut = (float) CashTransaction::query()->where('direction', 'out')->sum('amount');
        $openDecisions = (int) DB::table('readiness_items')->where('is_active', true)->whereNotIn('id', DB::table('readiness_checks')->where('is_completed', true)->pluck('readiness_item_id'))->count();

        return view('pages.admin.dashboard', [
            'queue' => Deposit::query()->where('status', 'submitted')->count()
                + User::query()->where('status', 'pending')->count()
                + Payout::query()->where('status', 'awaiting_approval')->count(),
            'held' => ProductOrder::query()->where('status', 'held_for_review')->count(),
            'cashBalance' => $cashIn - $cashOut,
            'openDecisions' => $openDecisions,
        ]);
    }

    public function approvals(): View
    {
        return view('pages.admin.approvals', [
            'users' => User::query()->with('residentProfile')->whereIn('status', ['pending', 'needs_info'])->latest()->paginate(10, ['*'], 'menunggu')->withQueryString(),
            'accounts' => User::query()->with(['residentProfile', 'roles'])->whereNotIn('status', ['pending', 'needs_info'])->latest()->paginate(10, ['*'], 'akun')->withQueryString(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $action = $request->validate([
            'action' => ['required', 'in:approve,needs_info,reject'],
        ])['action'];

        if ($user->id === $request->user()->id) {
            return back()->with('warning', 'Anda tidak dapat mengubah status akun yang sedang dipakai.');
        }

        if ($action === 'needs_info') {
            $user->update(['status' => 'needs_info']);
            $user->residentProfile?->update(['status' => 'needs_info']);

            return back()->with('success', 'Data tambahan diminta kepada '.$user->name.'.');
        }

        if ($action === 'reject') {
            $user->update(['status' => 'rejected', 'login_enabled' => false]);
            $user->residentProfile?->update(['status' => 'rejected']);

            return back()->with('success', 'Pendaftaran '.$user->name.' ditolak.');
        }

        $next = ((int) ResidentProfile::query()->where('resident_code', 'like', 'W-%')->pluck('resident_code')->map(fn ($code) => (int) str_replace('W-', '', (string) $code))->max()) + 1;
        $code = $user->user_code ?: 'W-'.str_pad((string) max($next, 1), 3, '0', STR_PAD_LEFT);

        $user->update([
            'status' => 'active',
            'user_code' => $code,
            'login_enabled' => true,
        ]);

        $user->residentProfile?->update([
            'status' => 'active',
            'resident_code' => $user->residentProfile->resident_code ?: $code,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', $user->name.' disetujui sebagai warga aktif.');
    }

    public function updateResidentStatus(Request $request, ResidentProfile $resident): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ])['status'];

        if ($resident->user_id === $request->user()->id) {
            return back()->with('warning', 'Anda tidak dapat menonaktifkan akun yang sedang dipakai.');
        }

        $active = $status === 'active';
        $email = (string) $resident->user?->email;
        $canLogin = $active && ! str_ends_with($email, '@warga.sirkula.local');

        $resident->update(['status' => $status]);
        $resident->user?->update([
            'status' => $status,
            'login_enabled' => $canLogin,
        ]);

        return back()->with('success', $active ? 'Akun warga diaktifkan kembali.' : 'Akun warga dinonaktifkan.');
    }

    public function roles(): View
    {
        return view('pages.admin.roles', [
            'roles' => Role::query()->where('slug', '!=', 'superadmin')->orderBy('name')->get(),
            'staff' => User::query()->where('status', 'active')->orderBy('name')->get(),
            'accounts' => User::query()->with('roles')->where('status', 'active')->orderBy('name')->paginate(10)->withQueryString(),
        ]);
    }

    public function assignRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'exists:roles,slug'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        abort_if($data['role'] === 'superadmin', 403);
        abort_if((int) $data['user_id'] === $request->user()->id, 403, 'Ubah role akun lain, bukan akun yang sedang dipakai.');

        $account = User::query()->findOrFail($data['user_id']);
        $role = Role::query()->where('slug', $data['role'])->firstOrFail();

        if ($account->roles()->where('roles.id', $role->id)->exists()) {
            return back()->with('warning', 'Role itu sudah ada pada akun tersebut.');
        }

        $account->roles()->attach($role->id, [
            'assigned_by' => $request->user()->id,
            'reason' => $data['reason'],
            'assigned_at' => now(),
        ]);

        return back()->with('success', 'Role '.$role->name.' ditambahkan untuk '.$account->name.'.');
    }

    public function tariff(): View
    {
        return view('pages.admin.tariff', [
            'rates' => MaterialRate::query()->with('material')->orderByDesc('effective_from')->paginate(10),
        ]);
    }

    public function publication(): View
    {
        return view('pages.admin.publication', [
            'events' => Event::query()->latest('event_date')->paginate(8, ['*'], 'kegiatan'),
            'products' => Product::query()->latest()->paginate(8, ['*'], 'produk'),
        ]);
    }

    public function fraud(): View
    {
        return view('pages.admin.fraud', [
            'deposits' => Deposit::query()->with(['residentProfile', 'material'])->where('status', 'fraud_flag')->latest()->paginate(10),
        ]);
    }

    public function cash(): View
    {
        return view('pages.admin.cash', [
            'rows' => CashTransaction::query()->orderByDesc('transaction_at')->paginate(12),
        ]);
    }

    public function import(): View
    {
        return view('pages.admin.import', [
            'batches' => ImportBatch::query()->latest()->paginate(10),
        ]);
    }

    public function readiness(): View
    {
        return view('pages.admin.readiness', [
            'items' => ReadinessItem::query()->orderBy('sort_order')->paginate(8),
        ]);
    }

    public function audit(): View
    {
        return view('pages.admin.audit', [
            'logs' => AuditLog::query()->latest('created_at')->paginate(15)->withQueryString(),
        ]);
    }

    public function backup(): View
    {
        return view('pages.admin.backup', [
            'tables' => [
                'users' => User::query()->count(),
                'resident_profiles' => ResidentProfile::query()->count(),
                'deposits' => Deposit::query()->count(),
                'rewards' => DB::table('rewards')->count(),
                'materials' => DB::table('materials')->count(),
            ],
        ]);
    }
}
