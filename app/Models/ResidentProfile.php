<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentProfile extends Model
{
    protected $fillable = [
        'user_id', 'resident_code', 'full_name', 'phone', 'rt', 'house_number',
        'household_name', 'display_name', 'leaderboard_visible', 'status',
        'created_by', 'approved_by', 'approved_at', 'review_note',
    ];

    protected function casts(): array
    {
        return ['leaderboard_visible' => 'boolean', 'approved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
