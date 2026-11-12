<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_code',
        'phone',
        'status',
        'login_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'login_enabled' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['assigned_by', 'reason', 'assigned_at', 'expires_at', 'revoked_at'])
            ->withTimestamps();
    }

    public function residentProfile(): HasOne
    {
        return $this->hasOne(ResidentProfile::class);
    }

    public function hasRole(string ...$slugs): bool
    {
        return $this->roles()
            ->wherePivotNull('revoked_at')
            ->whereIn('slug', $slugs)
            ->exists();
    }

    public function primaryRoleSlug(): ?string
    {
        return $this->roles()->wherePivotNull('revoked_at')->value('slug')
            ?? $this->roles()->value('slug');
    }

    public function primaryRoleName(): string
    {
        return $this->roles()->wherePivotNull('revoked_at')->value('name')
            ?? $this->roles()->value('name')
            ?? 'Pengguna';
    }
}
