<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Audit
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public static function record(string $action, ?Model $model = null, ?array $before = null, ?array $after = null, ?string $reason = null): void
    {
        $user = auth()->user();
        $request = request();

        (new AuditLog)->forceFill([
            'user_id' => $user?->id,
            'role_name' => $user?->primaryRoleName(),
            'action' => $action,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'before_data' => $before,
            'after_data' => $after,
            'reason' => $reason,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? substr((string) $request->userAgent(), 0, 500) : null,
            'created_at' => now(),
        ])->save();
    }
}
