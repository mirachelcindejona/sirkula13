<?php

namespace App\Services\Residents;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Pencatatan lintas-modul melalui satu adapter tanpa mengubah AuditLog milik core. */
final class ResidentAudit
{
    public static function record(?User $actor, string $action, string $model, int $id, ?array $before, ?array $after, ?string $reason = null): void
    {
        // Jangan mencatat nomor HP, nama lengkap, atau kata sandi ke audit tanpa kebutuhan.
        DB::table('audit_logs')->insert([
            'user_id' => $actor?->id,
            'role_name' => $actor ? (DB::table('role_user')->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $actor->id)->whereNull('role_user.revoked_at')
                ->value('roles.slug')) : null,
            'action' => $action,
            'auditable_type' => $model,
            'auditable_id' => $id,
            'before_data' => $before ? json_encode($before, JSON_THROW_ON_ERROR) : null,
            'after_data' => $after ? json_encode($after, JSON_THROW_ON_ERROR) : null,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
            'user_agent' => mb_substr((string) request()?->userAgent(), 0, 2000),
            'created_at' => now(),
        ]);
    }
}
