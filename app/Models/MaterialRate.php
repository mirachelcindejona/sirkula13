<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRate extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'buy_price_per_kg' => 'decimal:2',
            'eco_points_per_kg' => 'decimal:2',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
