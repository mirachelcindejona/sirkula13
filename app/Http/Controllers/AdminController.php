<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashTransaction;
use App\Models\MaterialRate;
use App\Models\ProductOrder;
use App\Models\ResidentProfile;
use App\Models\Role;
use App\Models\User;
use App\Support\Listing;
use App\Support\Notices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $cashIn = (float) CashTransaction::query()->where('direction', 'in')->sum('amount');
        $cashOut = (float) CashTransaction::query()->where('direction', 'out')->sum('amount');

        return view('pages.admin.dashboard', [
            'queue' => User::query()->whereIn('status', ['pending', 'needs_info'])->count(),
            'held' => ProductOrder::query()->where('status', 'held_for_review')->count(),
            'cashBalance' => $cashIn - $cashOut,
        ]);
    }

    public function approvals(): View
    {
        $pending = User::query()->with('residentProfile')->whereIn('status', ['pending', 'needs_info'])->latest();
        $accounts = User::query()->with(['residentProfile', 'roles'])->whereNotIn('status', ['pending', 'needs_info'])->latest();
        Listing::search($pending, ['name', 'email', 'phone']);
        Listing::search($accounts, ['name', 'email', 'phone']);

        return view('pages.admin.approvals', [
            'users' => $pending->listing('menunggu'),
            'accounts' => $accounts->listing('akun'),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $action = $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ])['action'];

        if ($user->id === $request->user()->id) {
            return back()->with('warning', 'Anda tidak dapat mengubah status akun yang sedang dipakai.');
        }

        if ($action === 'reject') {
            $user->update(['status' => 'rejected', 'login_enabled' => false]);
            $user->residentProfile?->update(['status' => 'rejected']);
            Notices::send($user, 'Pendaftaran ditolak', 'Pendaftaran akun Anda ditolak pengurus. Hubungi pengurus RW jika data yang dikirim perlu diperbaiki.', route('login'));

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
        Notices::send($user, 'Akun disetujui', 'Akun Anda sudah disetujui. ID warga terbit dan layanan warga bisa dipakai.', route('citizen.profile'));

        return back()->with('success', $user->name.' disetujui sebagai warga aktif.');
    }

    public function updateResidentStatus(Request $request, ResidentProfile $resident): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ])['status'];

        if ($resident->status === 'rejected' || $resident->user?->status === 'rejected') {
            return back()->with('error', 'Akun yang ditolak tidak dapat diubah statusnya.');
        }

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
        Notices::toProfile(
            $resident,
            $active ? 'Akun diaktifkan' : 'Akun dinonaktifkan',
            $active
                ? 'Akun Anda diaktifkan kembali. Layanan warga bisa dipakai seperti semula.'
                : 'Akun Anda dinonaktifkan pengurus. Hubungi pengurus RW jika ini tidak sesuai.',
            route('citizen.profile')
        );

        return back()->with('success', $active ? 'Akun warga diaktifkan kembali.' : 'Akun warga dinonaktifkan.');
    }

    public function roles(): View
    {
        return view('pages.admin.roles', [
            'roles' => Role::query()->where('slug', '!=', 'superadmin')->orderBy('name')->get(),
            'staff' => User::query()->where('status', 'active')->orderBy('name')->get(),
            'accounts' => tap(User::query()->with('roles')->where('status', 'active')->orderBy('name'), fn ($query) => Listing::search($query, ['name', 'email']))->listing(),
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
        Notices::send($account, 'Tugas baru', 'Anda mendapat tugas '.$role->name.'. '.$data['reason'], route('app.home'));

        return back()->with('success', 'Role '.$role->name.' ditambahkan untuk '.$account->name.'.');
    }

    public function tariff(): View
    {
        $materials = \App\Models\Material::query()->where('is_active', true)->orderBy('name')->get();
        $current = MaterialRate::query()->orderByDesc('effective_from')->get()->unique('material_id')->keyBy('material_id');

        return view('pages.admin.tariff', [
            'materials' => $materials,
            'current' => $current,
            'history' => tap(MaterialRate::query()->with('material')->orderByDesc('effective_from'), function ($query): void {
                Listing::search($query, [], ['material' => 'name']);
                if ($status = Listing::oneOf('status', ['draft', 'approved'])) {
                    $query->where('status', $status);
                }
            })->listing(),
        ]);
    }

    public function storeTariff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'price' => ['required', 'array'],
            'price.*' => ['required', 'numeric', 'min:0'],
            'points' => ['required', 'array'],
            'points.*' => ['required', 'numeric', 'min:0', 'multiple_of:2'],
            'effective_from' => ['required', 'date'],
            'official' => ['required', 'in:0,1'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $status = $data['official'] === '1' ? 'approved' : 'draft';
        foreach ($data['points'] as $materialId => $points) {
            $rate = MaterialRate::query()->firstOrNew([
                'material_id' => $materialId,
                'effective_from' => $data['effective_from'],
            ]);
            $before = $rate->exists ? $rate->only(['buy_price_per_kg', 'eco_points_per_kg', 'status']) : null;
            $rate->forceFill([
                'buy_price_per_kg' => $data['price'][$materialId] ?? 0,
                'eco_points_per_kg' => (int) round((float) $points),
                'donation_multiplier' => 1.5,
                'status' => $status,
                'approved_by' => $status === 'approved' ? $request->user()->id : null,
                'reason' => $data['reason'],
            ])->save();
            \App\Support\Audit::record('Mengubah tarif material', $rate, $before, $rate->only(['buy_price_per_kg', 'eco_points_per_kg', 'status']), $data['reason']);
        }

        return back()->with('success', 'Tarif disimpan. Poin donasi dihitung otomatis 1,5 kali poin jual.');
    }

    public function fraud(): View
    {
        return view('pages.admin.fraud', [
            'orders' => tap(ProductOrder::query()->with(['product', 'buyer', 'seller'])->where('status', 'held_for_review')->latest(), fn ($query) => Listing::search($query, ['reference_code', 'held_reason'], ['product' => 'name', 'buyer' => 'full_name']))->listing(),
        ]);
    }

    public function audit(): View
    {
        return view('pages.admin.audit', [
            'logs' => tap(AuditLog::query()->latest('created_at'), fn ($query) => Listing::search($query, ['action', 'role_name', 'reason']))->listing(),
        ]);
    }

}
