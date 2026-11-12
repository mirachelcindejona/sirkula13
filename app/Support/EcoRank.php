<?php

namespace App\Support;

class EcoRank
{
    /**
     * Ambang rank pada prototype. Total dihitung dari EcoPoints positif,
     * bukan dari saldo setelah penukaran reward.
     *
     * @return array<int, array{name: string, emoji: string, min: int, next: int|null}>
     */
    public static function tiers(): array
    {
        return [
            ['name' => 'Bronze', 'emoji' => '🥉', 'min' => 0, 'next' => 1000],
            ['name' => 'Silver', 'emoji' => '🥈', 'min' => 1000, 'next' => 2500],
            ['name' => 'Gold', 'emoji' => '🥇', 'min' => 2500, 'next' => 5000],
            ['name' => 'Platinum', 'emoji' => '💎', 'min' => 5000, 'next' => 9000],
            ['name' => 'Sirkula Champion', 'emoji' => '🏆', 'min' => 9000, 'next' => null],
        ];
    }

    /**
     * @return array{name: string, emoji: string, min: int, next: int|null}
     */
    public static function forTotal(int $total): array
    {
        $matched = self::tiers()[0];

        foreach (self::tiers() as $tier) {
            if ($total >= $tier['min']) {
                $matched = $tier;
            }
        }

        return $matched;
    }
}
