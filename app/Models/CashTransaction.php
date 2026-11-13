<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'transaction_at' => 'datetime',
            'posted_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function canRevise(): bool
    {
        return $this->created_at !== null && $this->created_at->gte(now()->subDay());
    }
}
