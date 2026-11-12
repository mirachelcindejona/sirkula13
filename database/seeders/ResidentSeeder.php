<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Keputusan dari prototipe; aman dijalankan ulang tanpa mereset cek operasional. */
class ResidentSeeder extends Seeder
{
    public function run(): void
    {
        $decisions = [
            ['Harga beli rupiah per kg', 'Tetapkan harga beli per kilogram, tanggal mulai berlaku, dan dasar penetapan melalui keputusan pengurus.'],
            ['Kapan uang dibayarkan kepada warga', 'Tetapkan pembayaran langsung atau antrean pembayaran, berikut bukti dan SOP.'],
            ['Pihak yang menyetujui pengeluaran/pembayaran', 'Tunjuk approver yang berbeda dari pembuat transaksi dan petugas pengganti.'],
            ['Pemilik proses katalog produk', 'Pastikan pemetaan katalog ke Pengelola Kegiatan diterima atau tunjuk role resmi lain.'],
            ['Bukti transaksi produk pembeli luar RW', 'Tetapkan bukti minimum dan pihak yang memeriksa sebelum poin penjual diberikan.'],
            ['Pembulatan poin pecahan', 'Konfirmasi aturan pembulatan yang konsisten pada web dan pencatatan manual.'],
            ['Tampilan nama pada leaderboard', 'Tetapkan nama/alias default dan cara warga atau donatur mengubah preferensi.'],
            ['Retensi bukti dan jadwal backup', 'Tentukan penanggung jawab, penyimpanan aman, lama retensi, dan prosedur pemulihan.'],
            ['Batas waktu sengketa produk', 'Tetapkan batas pemeriksaan serta kanal pengiriman bukti/keberatan.'],
            ['Tarif lama saat pencatatan offline', 'Tentukan cara membuktikan tarif yang berlaku pada tanggal transaksi dan approver-nya.'],
            ['Ketersediaan kas pembayaran warga', 'Tentukan prioritas antrean dan cara melaporkan total yang masih terutang.'],
            ['Akses laporan rinci untuk pengurus RW', 'Pilih role resmi sesuai tugas dan kebutuhan minimum; jangan menggunakan akun bersama.'],
            ['Konfirmasi pilihan Jual atau Donasi', 'Sepakati bahwa pilihan ditampilkan, dikonfirmasi warga, dan tidak diganti tanpa catatan.'],
        ];

        DB::transaction(function () use ($decisions): void {
            foreach ($decisions as $index => [$title, $description]) {
                $code = sprintf('DEC-%02d', $index + 1);
                DB::table('readiness_items')->updateOrInsert(['code' => $code], [
                    'category' => 'Keputusan operasional', 'title' => $title,
                    'description' => $description, 'sort_order' => $index + 1,
                    'is_active' => true, 'updated_at' => now(), 'created_at' => now(),
                ]);
                $id = DB::table('readiness_items')->where('code', $code)->value('id');
                if (! DB::table('readiness_checks')->where('readiness_item_id', $id)->exists()) {
                    DB::table('readiness_checks')->insert([
                        'readiness_item_id' => $id, 'is_completed' => false,
                        'note' => null, 'completed_by' => null, 'completed_at' => null,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
