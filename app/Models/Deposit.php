<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deposit extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:3',
            'money_amount' => 'decimal:2',
            'accepted' => 'boolean',
            'resident_choice_confirmed' => 'boolean',
            'price_official_at_creation' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function residentProfile(): BelongsTo
    {
        return $this->belongsTo(ResidentProfile::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(MaterialRate::class, 'material_rate_id');
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }
}
