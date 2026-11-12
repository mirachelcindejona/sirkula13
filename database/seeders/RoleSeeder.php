<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Warga', 'slug' => 'warga', 'description' => 'Akun warga RW 13.'],
            ['name' => 'Operator Bank Sampah', 'slug' => 'operator-bank-sampah', 'description' => 'Mengelola setoran, stok, dan penyaluran material.'],
            ['name' => 'Pengelola Kegiatan', 'slug' => 'pengelola-kegiatan', 'description' => 'Mengelola kegiatan, edukasi, katalog, dan transaksi produk.'],
            ['name' => 'Bendahara', 'slug' => 'bendahara', 'description' => 'Mengelola donasi uang, pembayaran, pengeluaran, dan buku kas.'],
            ['name' => 'Superadmin', 'slug' => 'superadmin', 'description' => 'Mengelola administrasi dan persetujuan lintas modul.'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                array_merge($role, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
