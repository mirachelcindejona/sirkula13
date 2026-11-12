<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
<<<<<<< HEAD
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
=======
    //
>>>>>>> 7d3f6c32ee16d6887a3af0c673ec9c2eb28ee2e5
}
