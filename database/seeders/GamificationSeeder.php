<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rank;
use App\Models\Reward;

class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat data dummy Ranks
        $ranks = [
            ['name' => 'Eco Newbie', 'min_xp' => 0, 'badge_icon' => 'seed.png'],
            ['name' => 'Eco Warrior', 'min_xp' => 100, 'badge_icon' => 'shield.png'],
            ['name' => 'Eco Master', 'min_xp' => 500, 'badge_icon' => 'crown.png'],
        ];

        foreach ($ranks as $rank) {
            Rank::firstOrCreate(['name' => $rank['name']], $rank);
        }

        // 2. Buat Data Dummy Rewards
        $rewards = [
            [
                'name' => 'Bibit Tanaman Cabai',
                'description' => 'Bibit tanaman cabai siap tanam untuk pekarangan rumah.',
                'points_cost' => 50,
                'stock' => 20,
            ],
            [
                'name' => 'Tas Belanja Ramah Lingkungan (Tote Bag)',
                'description' => 'Tote bag kanvas tebal untuk mengurangi penggunaan kantong plastik.',
                'points_cost' => 100,
                'stock' => 15,
            ],
            [
                'name' => 'Tumbler / Botol Minum Stainless Steel',
                'description' => 'Botol minum tahan panas dan dingin untuk mengurangi botol plastik sekali pakai.',
                'points_cost' => 250,
                'stock' => 10,
            ],
        ];

        foreach ($rewards as $reward) {
            Reward::firstOrCreate(['name' => $reward['name']], $reward);
        }
    }
}
