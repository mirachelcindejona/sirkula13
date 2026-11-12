<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_xp',
        'badge_icon',
        'description',
    ];

    /**
     * Relasi: Rank dimiliki oleh banyak User (Warga).
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
