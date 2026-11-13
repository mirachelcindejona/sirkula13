<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Payout extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function residentProfile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ResidentProfile::class);
    }

    public function deposit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function cashOuts(): MorphMany
    {
        return $this->morphMany(CashTransaction::class, 'cashable')
            ->where('direction', 'out')
            ->whereNull('reversed_at')
            ->latest();
    }

    public function remaining(): float
    {
        return max(0, round((float) $this->amount - (float) $this->paid_amount, 2));
    }

    public function displayStatus(): string
    {
        if ($this->status === 'paid') {
            return 'paid';
        }

        return (float) $this->paid_amount > 0 ? 'partial' : (string) $this->status;
    }

    public function reversibleCash(): ?CashTransaction
    {
        $cash = $this->cashOuts->first();

        return $cash && $cash->canRevise() ? $cash : null;
    }
}
