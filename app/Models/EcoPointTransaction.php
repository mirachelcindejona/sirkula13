<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EcoPointTransaction extends Model
{
    protected $fillable = [
        'reference_code',
        'resident_profile_id',
        'points_delta',
        'kind',
        'description',
        'pointable_type',
        'pointable_id',
        'created_by',
        'approved_by',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'points_delta' => 'integer',
        ];
    }

    public function residentProfile(): BelongsTo
    {
        return $this->belongsTo(ResidentProfile::class);
    }

    public function pointable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
