<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'points_required',
        'stock',
        'image_path',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'points_required' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function availableStock(): int
    {
        $reserved = $this->redemptions()->where('status', 'pending_pickup')->count();

        return max(0, $this->stock - $reserved);
    }
}
