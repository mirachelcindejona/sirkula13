<?php

namespace App\Services\Residents;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ResidentAccess
{
    public static function hasRole(User $user, string ...$roles): bool
    {
        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->whereNull('role_user.revoked_at')
            ->where(fn ($q) => $q->whereNull('role_user.expires_at')->orWhere('role_user.expires_at', '>', now()))
            ->whereIn('roles.slug', $roles)->exists();
    }

    public static function require(User $user, string ...$roles): void
    {
        abort_unless($user->status === 'active' && $user->login_enabled && self::hasRole($user, ...$roles), 403);
    }
}
