<?php

namespace App\Models;

use App\Support\EcoRank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResidentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'resident_code',
        'full_name',
        'phone',
        'rt',
        'house_number',
        'household_name',
        'display_name',
        'leaderboard_visible',
        'donor_display',
        'linked_profile_id',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'leaderboard_visible' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function linkedProfile(): BelongsTo
    {
        return $this->belongsTo(self::class, 'linked_profile_id');
    }

    public function ecoPointTransactions(): HasMany
    {
        return $this->hasMany(EcoPointTransaction::class);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function donor(): HasOne
    {
        return $this->hasOne(Donor::class);
    }

    public function earnedPoints(): int
    {
        return (int) $this->ecoPointTransactions()->where('points_delta', '>', 0)->sum('points_delta');
    }

    public function pointBalance(): int
    {
        return (int) $this->ecoPointTransactions()->sum('points_delta');
    }

    public function reservedPoints(): int
    {
        return (int) $this->rewardRedemptions()->where('status', 'pending_pickup')->sum('points_cost');
    }

    public function availablePoints(): int
    {
        return $this->pointBalance() - $this->reservedPoints();
    }

    /**
     * @return array{name: string, emoji: string, min: int, next: int|null}
     */
    public function rank(): array
    {
        return EcoRank::forTotal($this->earnedPoints());
    }
}
