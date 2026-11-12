<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReadinessItem extends Model
{
    protected $fillable = ['code', 'category', 'title', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function check(): HasOne
    {
        return $this->hasOne(ReadinessCheck::class);
    }
}
