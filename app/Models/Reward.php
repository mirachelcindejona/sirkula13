<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'points_required',
        'stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi: Reward bisa diklaim dalam banyak transaksi penukaran (Redemption).
     */
    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }
    //
}
