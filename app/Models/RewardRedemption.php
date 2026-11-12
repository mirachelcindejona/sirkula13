<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'redemption_code',
        'user_id',
        'reward_id',
        'points_spent',
        'status', // 'pending', 'approved', 'completed', 'rejected'
        'redeemed_at',
        'notes',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    /**
     * Relasi: Penukaran dilakukan oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Penukaran merujuk pada satu Reward tertentu.
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
    //
}
