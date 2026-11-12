<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        // Nilai diambil dari MATERIALS dan STARTER_PRICES pada prototype.
        // Tarif harga beli ditandai draft karena prototype menyatakan priceOfficial=false.
        $materials = [
            ['code' => 'kardus', 'name' => 'Kardus atau karton bersih', 'short_name' => 'Kardus', 'acceptance_standard' => 'Bersih, kering, tidak berminyak.', 'icon' => '📦', 'buy_price' => 1500, 'sell_points' => 50],
            ['code' => 'pet_bening', 'name' => 'Botol PET bening', 'short_name' => 'Botol PET bening', 'acceptance_standard' => 'Kosong, tidak tercampur cairan, relatif bersih.', 'icon' => '🧴', 'buy_price' => 2500, 'sell_points' => 80],
            ['code' => 'pet_warna', 'name' => 'Botol PET berwarna', 'short_name' => 'Botol PET berwarna', 'acceptance_standard' => 'Kosong dan dipisahkan dari PET bening.', 'icon' => '🧴', 'buy_price' => 1600, 'sell_points' => 60],
            ['code' => 'galon', 'name' => 'Galon plastik yang diterima', 'short_name' => 'Galon plastik', 'acceptance_standard' => 'Jenis galon mengikuti standar penerimaan petugas.', 'icon' => '🫙', 'buy_price' => 2200, 'sell_points' => 70],
            ['code' => 'aluminium', 'name' => 'Kaleng aluminium', 'short_name' => 'Kaleng aluminium', 'acceptance_standard' => 'Kosong dan tidak berisi bahan berbahaya.', 'icon' => '🥫', 'buy_price' => 10000, 'sell_points' => 120],
            ['code' => 'besi', 'name' => 'Kaleng besi atau seng', 'short_name' => 'Besi/seng', 'acceptance_standard' => 'Kosong, tidak tercampur benda berbahaya.', 'icon' => '🥫', 'buy_price' => 2000, 'sell_points' => 50],
            ['code' => 'plastik_keras', 'name' => 'Plastik keras lainnya', 'short_name' => 'Plastik keras', 'acceptance_standard' => 'Hanya jenis yang diterima petugas.', 'icon' => '♻️', 'buy_price' => 1200, 'sell_points' => 40],
        ];

        $effectiveFrom = '2026-10-30';

        foreach ($materials as $item) {
            DB::table('materials')->updateOrInsert(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'short_name' => $item['short_name'],
                    'acceptance_standard' => $item['acceptance_standard'],
                    'icon' => $item['icon'],
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $materialId = DB::table('materials')->where('code', $item['code'])->value('id');

            DB::table('material_rates')->updateOrInsert(
                ['material_id' => $materialId, 'effective_from' => $effectiveFrom],
                [
                    'buy_price_per_kg' => $item['buy_price'],
                    'eco_points_per_kg' => $item['sell_points'],
                    'donation_multiplier' => 1.50,
                    'effective_to' => null,
                    'status' => 'draft',
                    'approved_by' => null,
                    'reason' => 'Tarif awal pada prototype; harga belum berstatus resmi (priceOfficial=false). Donasi memperoleh bonus EcoPoints 50%.',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
