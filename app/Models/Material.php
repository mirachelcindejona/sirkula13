<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    /**
     * Nama tabel database.
     */
    protected $table = 'materials';

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'code',
        'name',
        'short_name',
        'acceptance_standard',
        'icon',
        'is_active',
    ];

    /**
     * Casting tipe data.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke tarif material.
     */
    public function rates(): HasMany
    {
        return $this->hasMany(MaterialRate::class, 'material_id');
    }

    /**
     * Relasi ke transaksi setoran.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'material_id');
    }

    /**
     * Relasi ke transaksi material keluar.
     */
    public function outflows(): HasMany
    {
        return $this->hasMany(MaterialOutflow::class, 'material_id');
    }
}
