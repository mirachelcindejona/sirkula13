<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRate extends Model
{
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
