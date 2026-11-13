<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function posterUrl(): string
    {
        if ($this->poster_path) {
            return \App\Support\Uploads::url($this->poster_path);
        }

        $fallback = str_contains(strtolower((string) $this->title), 'pilah')
            ? 'images/events/kelas-pilah.jpg'
            : 'images/events/kerja-bakti.jpg';

        return asset($fallback);
    }

    public function hasStarted(): bool
    {
        return $this->event_date !== null && $this->event_date->copy()->startOfDay()->lt(now()->startOfDay());
    }
}
