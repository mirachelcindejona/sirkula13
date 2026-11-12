<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadinessCheck extends Model
{
    protected $fillable = ['readiness_item_id', 'is_completed', 'completed_by', 'completed_at', 'note'];

    protected function casts(): array
    {
        return ['is_completed' => 'boolean', 'completed_at' => 'datetime'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ReadinessItem::class, 'readiness_item_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
