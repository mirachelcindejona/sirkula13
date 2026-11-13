<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialOutflow extends Model
{
    protected $guarded = [];

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
