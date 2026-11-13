<?php

namespace App\Support;

use App\Models\Notice;
use App\Models\ResidentProfile;
use App\Models\User;

class Notices
{
    public static function send(?User $user, string $title, string $body, ?string $url = null): void
    {
        if (! $user) {
            return;
        }

        Notice::query()->create([
            'user_id' => $user->id,
            'title' => mb_substr($title, 0, 160),
            'body' => mb_substr($body, 0, 500),
            'url' => $url,
        ]);
    }

    public static function toProfile(?ResidentProfile $profile, string $title, string $body, ?string $url = null): void
    {
        $profile?->loadMissing('user');
        self::send($profile?->user, $title, $body, $url);
    }
}
