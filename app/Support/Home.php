<?php

namespace App\Support;

use App\Models\User;

class Home
{
    public static function routeName(User $user): string
    {
        if ($user->hasRole('superadmin')) {
            return 'admin.dashboard';
        }

        return match ($user->primaryRoleSlug()) {
            'operator-bank-sampah' => 'bank.dashboard',
            'pengelola-kegiatan' => 'program.dashboard',
            'bendahara' => 'finance.dashboard',
            default => 'app.home',
        };
    }
}
