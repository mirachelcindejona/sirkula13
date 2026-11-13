<?php

namespace Database\Seeders;

use App\Support\EcoRank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SirkulaDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('SirkulaDemoSeeder hanya boleh dijalankan pada environment development atau testing.');
        }

        DB::transaction(function (): void {
            $now = now();
            $ago = fn (int $days) => now()->subDays($days)->toDateTimeString();

            /* -------------------------------------------------------------
             * 1. Akun dan role: mengikuti daftar users pada makeSeed().
             * Password "password" hanya untuk database development/demo.
             * ----------------------------------------------------------- */
            $accountRows = [
                ['ref' => 'W-001', 'user_code' => 'W-001', 'name' => 'Ibu Sari Wulandari', 'email' => 'sari@sirkula.test', 'phone' => '081234560001', 'role' => 'warga', 'status' => 'active'],
                ['ref' => 'W-002', 'user_code' => 'W-002', 'name' => 'Bapak Budi Santoso', 'email' => 'budi@sirkula.test', 'phone' => '081234560002', 'role' => 'warga', 'status' => 'active'],
                ['ref' => 'W-003', 'user_code' => 'W-003', 'name' => 'Ibu Diah Lestari', 'email' => 'diah@sirkula.test', 'phone' => '081234560003', 'role' => 'warga', 'status' => 'active'],
                ['ref' => 'OP-001', 'user_code' => 'OP-001', 'name' => 'Pak Rudi — Operator', 'email' => 'rudi@sirkula.test', 'phone' => '081234560011', 'role' => 'operator-bank-sampah', 'status' => 'active'],
                ['ref' => 'OP-002', 'user_code' => 'OP-002', 'name' => 'Bu Yanti — Operator', 'email' => 'yanti@sirkula.test', 'phone' => '081234560012', 'role' => 'operator-bank-sampah', 'status' => 'active'],
                ['ref' => 'PK-001', 'user_code' => 'PK-001', 'name' => 'Teh Maya — Pengelola Kegiatan', 'email' => 'maya@sirkula.test', 'phone' => '081234560021', 'role' => 'pengelola-kegiatan', 'status' => 'active'],
                ['ref' => 'PK-002', 'user_code' => 'PK-002', 'name' => 'Bu Nisa — Pengelola Kegiatan', 'email' => 'nisa@sirkula.test', 'phone' => '081234560022', 'role' => 'pengelola-kegiatan', 'status' => 'active'],
                ['ref' => 'BEN-001', 'user_code' => 'BEN-001', 'name' => 'Bu Lina — Bendahara', 'email' => 'lina@sirkula.test', 'phone' => '081234560031', 'role' => 'bendahara', 'status' => 'active'],
                ['ref' => 'ADM-001', 'user_code' => 'ADM-001', 'name' => 'Admin SIRKULA — Superadmin', 'email' => 'admin@sirkula.test', 'phone' => '081234560041', 'role' => 'superadmin', 'status' => 'active'],
                ['ref' => 'PEND-001', 'user_code' => null, 'name' => 'Rina Apriliyani', 'email' => 'rina@sirkula.test', 'phone' => '081234560051', 'role' => 'warga', 'status' => 'pending'],
                ['ref' => 'PEND-002', 'user_code' => null, 'name' => 'Deni Kurniawan', 'email' => 'deni@sirkula.test', 'phone' => '081234560052', 'role' => 'warga', 'status' => 'needs_info'],
            ];

            foreach ($accountRows as $account) {
                if (empty($account['email']) || ! filter_var($account['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException('Setiap akun demo wajib memiliki alamat email valid: '.($account['ref'] ?? 'akun tanpa kode'));
                }
            }

            $users = [];
            foreach ($accountRows as $account) {
                $userData = [
                    'user_code' => $account['user_code'],
                    'name' => $account['name'],
                    // Prototype memakai 123456 bila akun demo belum punya kata sandi sendiri.
                    'password' => Hash::make('123456'),
                    'status' => $account['status'],
                    'login_enabled' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                    'email' => $account['email'],
                    'email_verified_at' => null,
                ];

                DB::table('users')->updateOrInsert(
                    ['phone' => $account['phone']],
                    $userData
                );

                $users[$account['ref']] = DB::table('users')
                    ->where('phone', $account['phone'])
                    ->value('id');
            }

            $roleIds = DB::table('roles')->pluck('id', 'slug')->all();
            foreach ($accountRows as $account) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $users[$account['ref']], 'role_id' => $roleIds[$account['role']]],
                    ['assigned_by' => null, 'reason' => 'Role awal akun demo prototype.', 'assigned_at' => $now, 'revoked_at' => null, 'updated_at' => $now, 'created_at' => $now]
                );
            }

            $residentRows = [
                ['ref' => 'W-001', 'code' => 'W-001', 'name' => 'Ibu Sari Wulandari', 'phone' => '081234560001', 'rt' => '03', 'house_number' => '12A', 'household' => 'Keluarga Wulandari', 'display' => 'Sari W.', 'status' => 'active', 'review_note' => null],
                ['ref' => 'W-002', 'code' => 'W-002', 'name' => 'Bapak Budi Santoso', 'phone' => '081234560002', 'rt' => '02', 'house_number' => null, 'household' => 'Keluarga Santoso', 'display' => 'Budi S.', 'status' => 'active', 'review_note' => null],
                ['ref' => 'W-003', 'code' => 'W-003', 'name' => 'Ibu Diah Lestari', 'phone' => '081234560003', 'rt' => '04', 'house_number' => null, 'household' => 'Keluarga Lestari', 'display' => 'Diah L.', 'status' => 'active', 'review_note' => null],
                ['ref' => 'PEND-001', 'code' => null, 'name' => 'Rina Apriliyani', 'phone' => '081234560051', 'rt' => '03', 'house_number' => null, 'household' => 'Keluarga Apriliyani', 'display' => 'Rina A.', 'status' => 'pending', 'review_note' => null],
                ['ref' => 'PEND-002', 'code' => null, 'name' => 'Deni Kurniawan', 'phone' => '081234560052', 'rt' => '02', 'house_number' => null, 'household' => 'Keluarga Kurniawan', 'display' => 'Deni K.', 'status' => 'needs_info', 'review_note' => 'Mohon konfirmasi alamat RT dan nomor kontak.'],
            ];

            $residents = [];
            foreach ($residentRows as $resident) {
                $userId = $users[$resident['ref']];
                DB::table('resident_profiles')->updateOrInsert(
                    ['user_id' => $userId],
                    [
                        'resident_code' => $resident['code'],
                        'full_name' => $resident['name'],
                        'phone' => $resident['phone'],
                        'rt' => $resident['rt'],
                        'house_number' => $resident['house_number'],
                        'household_name' => $resident['household'],
                        'display_name' => $resident['display'],
                        'leaderboard_visible' => true,
                        'donor_display' => 'alias',
                        'status' => $resident['status'],
                        'created_by' => null,
                        'approved_by' => null,
                        'approved_at' => null,
                        'review_note' => $resident['review_note'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
                $residents[$resident['ref']] = DB::table('resident_profiles')->where('user_id', $userId)->value('id');
            }

            $materialIds = DB::table('materials')->pluck('id', 'code')->all();

            /* -------------------------------------------------------------
             * 2. Setoran dan pencairan warga.
             * ----------------------------------------------------------- */
            $depositRows = [
                ['ref' => 'SET-2601', 'resident' => 'W-001', 'material' => 'pet_bening', 'weight' => 1.500, 'type' => 'sale', 'price' => 2500, 'amount' => 3750, 'points' => 120, 'status' => 'verified', 'creator' => 'OP-001', 'verifier' => 'OP-002', 'days' => 15],
                ['ref' => 'SET-2602', 'resident' => 'W-001', 'material' => 'kardus', 'weight' => 2.000, 'type' => 'donation', 'price' => 0, 'amount' => 0, 'points' => 150, 'status' => 'verified', 'creator' => 'OP-002', 'verifier' => 'OP-001', 'days' => 12],
                ['ref' => 'SET-2603', 'resident' => 'W-002', 'material' => 'pet_bening', 'weight' => 2.000, 'type' => 'sale', 'price' => 2500, 'amount' => 5000, 'points' => 160, 'status' => 'verified', 'creator' => 'OP-001', 'verifier' => 'OP-002', 'days' => 8],
                ['ref' => 'SET-2604', 'resident' => 'W-003', 'material' => 'aluminium', 'weight' => 0.500, 'type' => 'sale', 'price' => 10000, 'amount' => 5000, 'points' => 60, 'status' => 'verified', 'creator' => 'OP-002', 'verifier' => 'OP-001', 'days' => 5],
                ['ref' => 'SET-2605', 'resident' => 'W-003', 'material' => 'kardus', 'weight' => 1.250, 'type' => 'donation', 'price' => 0, 'amount' => 0, 'points' => 94, 'status' => 'verified', 'accepted' => true, 'creator' => 'OP-001', 'verifier' => 'OP-001', 'days' => 0],
                ['ref' => 'SET-2606', 'resident' => 'W-002', 'material' => 'pet_warna', 'weight' => 0.800, 'type' => 'sale', 'price' => 1600, 'amount' => 0, 'points' => 0, 'status' => 'verified', 'accepted' => false, 'creator' => 'OP-002', 'verifier' => 'OP-002', 'days' => 1],
            ];

            $deposits = [];
            $sellPoints = ['kardus' => 50, 'pet_bening' => 80, 'pet_warna' => 60, 'galon' => 70, 'aluminium' => 120, 'besi' => 50, 'plastik_keras' => 40];
            $donatePoints = ['kardus' => 75, 'pet_bening' => 120, 'pet_warna' => 90, 'galon' => 105, 'aluminium' => 180, 'besi' => 75, 'plastik_keras' => 60];
            foreach ($depositRows as $row) {
                $createdAt = $ago($row['days']);
                $pointsPerKg = $row['type'] === 'donation'
                    ? $donatePoints[$row['material']]
                    : $sellPoints[$row['material']];
                DB::table('deposits')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'resident_profile_id' => $residents[$row['resident']],
                        'external_name' => null,
                        'material_id' => $materialIds[$row['material']],
                        'material_rate_id' => null, // snapshot historis tidak ditautkan ke tarif draft saat ini
                        'type' => $row['type'],
                        'weight_kg' => $row['weight'],
                        'unit_snapshot' => 'kg',
                        'price_per_kg_snapshot' => $row['price'],
                        'price_effective_date_snapshot' => '2026-10-01',
                        'price_official_at_creation' => true,
                        'eco_points_per_kg_snapshot' => $pointsPerKg,
                        'donation_multiplier_snapshot' => 1.50,
                        'money_amount' => $row['amount'],
                        'eco_points_awarded' => $row['points'],
                        'accepted' => $row['accepted'] ?? true,
                        'quality_note' => ($row['accepted'] ?? true) ? 'Sesuai standar penerimaan' : 'Material tidak sesuai standar penerimaan.',
                        'resident_choice_confirmed' => true,
                        'status' => $row['status'],
                        'source' => 'online',
                        'offline_transaction_id' => null,
                        'note' => 'Transaksi tercatat',
                        'created_by' => $users[$row['creator']],
                        'verified_by' => $row['verifier'] ? $users[$row['verifier']] : null,
                        'verified_at' => $createdAt,
                        'corrected_by' => null,
                        'correction_reason' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );
                $deposits[$row['ref']] = DB::table('deposits')->where('reference_code', $row['ref'])->value('id');
            }

            $payoutRows = [
                ['ref' => 'PAY-HIST-01', 'deposit' => 'SET-2601', 'resident' => 'W-001', 'amount' => 3750, 'status' => 'paid', 'creator' => 'OP-001', 'approver' => 'ADM-001', 'payer' => 'BEN-001', 'proof' => 'BUKTI-2601', 'days' => 15],
                ['ref' => 'PAY-2602', 'deposit' => 'SET-2603', 'resident' => 'W-002', 'amount' => 5000, 'status' => 'saved', 'creator' => 'OP-001', 'approver' => null, 'payer' => null, 'proof' => null, 'days' => 1],
            ];
            $payouts = [];
            foreach ($payoutRows as $row) {
                $createdAt = $ago($row['days']);
                DB::table('payouts')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'deposit_id' => $deposits[$row['deposit']],
                        'resident_profile_id' => $residents[$row['resident']],
                        'amount' => $row['amount'],
                        'paid_amount' => $row['status'] === 'paid' ? $row['amount'] : 0,
                        'status' => $row['status'],
                        'created_by' => $users[$row['creator']],
                        'approved_by' => $row['approver'] ? $users[$row['approver']] : null,
                        'paid_by' => $row['payer'] ? $users[$row['payer']] : null,
                        'proof_reference' => $row['proof'],
                        'note' => null,
                        'approved_at' => $row['approver'] ? $createdAt : null,
                        'paid_at' => $row['status'] === 'paid' ? $createdAt : null,
                        'updated_at' => $now,
                        'created_at' => $createdAt,
                    ]
                );
                $payouts[$row['ref']] = DB::table('payouts')->where('reference_code', $row['ref'])->value('id');
            }

            /* -------------------------------------------------------------
             * 3. Kegiatan, pendaftaran, kehadiran, relawan, edukasi.
             * ----------------------------------------------------------- */
            $eventRows = [
                ['ref' => 'EV-001', 'category' => 'Kegiatan', 'title' => 'Kerja Bakti Minggu Bersih', 'date' => '2026-11-07', 'time' => '07.00–09.00', 'location' => 'Balai RW 13 dan lingkungan sekitar', 'quota' => 40, 'description' => 'Bersihkan lingkungan dan pilah material yang masih dapat dimanfaatkan. Gunakan pakaian nyaman dan bawa botol minum sendiri.', 'status' => 'published', 'creator' => 'PK-001', 'days' => 3],
                ['ref' => 'EV-002', 'category' => 'Edukasi', 'title' => 'Kelas Pilah dari Rumah', 'date' => '2026-11-14', 'time' => '09.00–10.30', 'location' => 'Balai RW 13', 'quota' => 25, 'description' => 'Belajar membedakan jenis material, menyiapkan setoran, serta memahami pilihan jual dan donasi.', 'status' => 'published', 'creator' => 'PK-001', 'days' => 2],
                ['ref' => 'EV-003', 'category' => 'Program lingkungan', 'title' => 'Tantangan Sudut Pilah Keluarga', 'date' => '2026-11-21', 'time' => '08.30–10.00', 'location' => 'Balai RW 13', 'quota' => 30, 'description' => 'Warga berbagi cara sederhana menyiapkan tempat pilah di rumah dan menjaga material tetap kering.', 'status' => 'published', 'creator' => 'PK-002', 'days' => 1],
                ['ref' => 'EV-004', 'category' => 'Kegiatan', 'title' => 'Pelatihan Kompos Skala Rumah', 'date' => '2026-11-28', 'time' => '09.00–11.00', 'location' => 'Aula RW 13', 'quota' => 20, 'description' => 'Pengenalan pengolahan sisa organik dan praktik dasar yang aman di rumah.', 'status' => 'draft', 'creator' => 'PK-002', 'days' => 0],
            ];
            $events = [];
            foreach ($eventRows as $row) {
                $createdAt = $ago($row['days']);
                DB::table('events')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'category' => $row['category'], 'title' => $row['title'], 'event_date' => $row['date'],
                        'event_time' => $row['time'], 'location' => $row['location'], 'quota' => $row['quota'],
                        'description' => $row['description'], 'poster_path' => null, 'attendance_points' => 50,
                        'volunteer_points' => 50, 'status' => $row['status'], 'created_by' => $users[$row['creator']],
                        'approved_by' => null, 'published_at' => $row['status'] === 'published' ? $createdAt : null,
                        'updated_at' => $now, 'created_at' => $createdAt,
                    ]
                );
                $events[$row['ref']] = DB::table('events')->where('reference_code', $row['ref'])->value('id');
            }

            $registrationRows = [
                ['ref' => 'REG-2601', 'event' => 'EV-001', 'resident' => 'W-001', 'days' => 2],
                ['ref' => 'REG-2602', 'event' => 'EV-001', 'resident' => 'W-002', 'days' => 1],
                ['ref' => 'REG-2603', 'event' => 'EV-002', 'resident' => 'W-003', 'days' => 1],
                ['ref' => 'REG-2604', 'event' => 'EV-003', 'resident' => 'W-001', 'days' => 0],
            ];
            foreach ($registrationRows as $row) {
                $createdAt = $ago($row['days']);
                DB::table('event_registrations')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    ['event_id' => $events[$row['event']], 'resident_profile_id' => $residents[$row['resident']], 'status' => 'registered', 'registered_at' => $createdAt, 'cancelled_at' => null, 'created_at' => $createdAt, 'updated_at' => $now]
                );
            }

            $attendanceRows = [
                ['ref' => 'ATT-2601', 'event' => 'EV-001', 'resident' => 'W-002', 'status' => 'pending_verification', 'creator' => 'PK-001', 'verifier' => null, 'days' => 0, 'note' => 'Check-in tercatat dari daftar peserta'],
                ['ref' => 'ATT-2602', 'event' => 'EV-002', 'resident' => 'W-003', 'status' => 'verified', 'creator' => 'PK-001', 'verifier' => 'ADM-001', 'days' => 2, 'note' => 'Kehadiran terkonfirmasi'],
            ];
            foreach ($attendanceRows as $row) {
                $createdAt = $ago($row['days']);
                DB::table('event_attendances')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'event_id' => $events[$row['event']], 'resident_profile_id' => $residents[$row['resident']],
                        'status' => $row['status'], 'checked_in_at' => $createdAt, 'created_by' => $users[$row['creator']],
                        'verified_by' => $row['verifier'] ? $users[$row['verifier']] : null,
                        'verified_at' => $row['verifier'] ? $ago(1) : null, 'note' => $row['note'],
                        'created_at' => $createdAt, 'updated_at' => $now,
                    ]
                );
            }

            /* -------------------------------------------------------------
             * 4. Produk, pesanan, reward, dan penukaran.
             * ----------------------------------------------------------- */
            $productRows = [
                ['code' => 'PR-001', 'name' => 'Pot tanaman dari kemasan', 'description' => 'Pot tanaman untuk sudut rumah, dibuat dari kemasan yang masih layak.', 'price' => 15000, 'stock' => 4, 'status' => 'published', 'creator' => 'PK-001', 'seller' => 'W-002', 'image_key' => 'pot', 'days' => 10],
                ['code' => 'PR-002', 'name' => 'Tas belanja guna ulang', 'description' => 'Tas belanja yang dapat digunakan berulang kali.', 'price' => 25000, 'stock' => 8, 'status' => 'published', 'creator' => 'PK-001', 'seller' => 'W-002', 'image_key' => 'bag', 'days' => 8],
                ['code' => 'PR-003', 'name' => 'Hiasan meja daur ulang', 'description' => 'Hiasan meja dari material yang masih dapat dimanfaatkan.', 'price' => 12000, 'stock' => 4, 'status' => 'published', 'creator' => 'PK-001', 'seller' => 'W-003', 'image_key' => 'decor', 'days' => 5],
            ];
            $products = [];
            $residentByRef = collect($residentRows)->keyBy('ref');
            foreach ($productRows as $row) {
                $createdAt = $ago($row['days']);
                $seller = $residentByRef[$row['seller']];
                DB::table('products')->updateOrInsert(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'], 'description' => $row['description'], 'price' => $row['price'],
                        'stock' => $row['stock'], 'image_path' => null, 'image_key' => $row['image_key'],
                        'status' => $row['status'], 'owner_kind' => 'warga',
                        'seller_profile_id' => $residents[$row['seller']],
                        'contact_name' => $seller['name'], 'contact_phone' => $seller['phone'],
                        'created_by' => $users[$row['creator']], 'approved_by' => null,
                        'published_at' => $createdAt, 'created_at' => $createdAt, 'updated_at' => $now,
                    ]
                );
                $products[$row['code']] = DB::table('products')->where('code', $row['code'])->value('id');
            }

            $orderRows = [
                ['ref' => 'ORD-2601', 'product' => 'PR-001', 'buyer' => 'W-001', 'seller' => 'W-002', 'qty' => 1, 'unit' => 15000, 'total' => 15000, 'status' => 'completed', 'seller_ok' => true, 'buyer_ok' => true, 'proof' => 'SERAH-2601', 'reason' => null, 'days' => 6],
                ['ref' => 'ORD-2602', 'product' => 'PR-002', 'buyer' => 'W-001', 'seller' => 'W-002', 'qty' => 1, 'unit' => 25000, 'total' => 25000, 'status' => 'awaiting_seller', 'seller_ok' => false, 'buyer_ok' => false, 'proof' => null, 'reason' => null, 'days' => 1],
                ['ref' => 'ORD-2603', 'product' => 'PR-003', 'buyer' => 'W-002', 'seller' => 'W-003', 'qty' => 1, 'unit' => 12000, 'total' => 12000, 'status' => 'awaiting_buyer', 'seller_ok' => true, 'buyer_ok' => false, 'proof' => 'SERAH-2603', 'reason' => null, 'days' => 2],
                ['ref' => 'ORD-2604', 'product' => 'PR-001', 'buyer' => 'W-001', 'seller' => 'W-002', 'qty' => 1, 'unit' => 15000, 'total' => 15000, 'status' => 'held_for_review', 'seller_ok' => true, 'buyer_ok' => false, 'proof' => 'SERAH-2604', 'reason' => 'Konfirmasi belum sesuai; perlu pemeriksaan pengelola.', 'days' => 3],
            ];
            $orders = [];
            foreach ($orderRows as $row) {
                $createdAt = $ago($row['days']);
                DB::table('product_orders')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'product_id' => $products[$row['product']], 'buyer_profile_id' => $residents[$row['buyer']],
                        'seller_profile_id' => $residents[$row['seller']], 'external_buyer_name' => null,
                        'external_buyer_phone' => null, 'is_external_buyer' => false, 'quantity' => $row['qty'],
                        'unit_price_snapshot' => $row['unit'], 'total_amount' => $row['total'],
                        'payment_method' => 'Kesepakatan langsung dengan penjual', 'points_used' => 0,
                        'status' => $row['status'], 'seller_confirmed' => $row['seller_ok'],
                        'buyer_confirmed' => $row['buyer_ok'],
                        'seller_confirmed_at' => $row['seller_ok'] ? $createdAt : null,
                        'buyer_confirmed_at' => $row['buyer_ok'] ? $createdAt : null,
                        'handover_proof' => $row['proof'], 'held_reason' => $row['reason'],
                        'reviewed_by' => null, 'verified_at' => null,
                        'created_by' => $users[$row['buyer']],
                        'completed_at' => $row['status'] === 'completed' ? $createdAt : null,
                        'created_at' => $createdAt, 'updated_at' => $now,
                    ]
                );
                $orders[$row['ref']] = DB::table('product_orders')->where('reference_code', $row['ref'])->value('id');
            }

            $rewardRows = [
                ['code' => 'RW-001', 'name' => 'Paket bibit tanaman', 'points' => 350, 'stock' => 10, 'description' => 'Paket bibit untuk penghijauan di rumah.', 'icon' => '🌱'],
                ['code' => 'RW-002', 'name' => 'Kantong belanja pakai ulang', 'points' => 500, 'stock' => 8, 'description' => 'Kantong pakai ulang untuk belanja sehari-hari.', 'icon' => '🛍️'],
                ['code' => 'RW-003', 'name' => 'Paket alat pilah rumah', 'points' => 750, 'stock' => 4, 'description' => 'Perlengkapan dasar untuk memilah material di rumah.', 'icon' => '♻️'],
            ];
            $rewards = [];
            foreach ($rewardRows as $row) {
                DB::table('rewards')->updateOrInsert(
                    ['code' => $row['code']],
                    ['name' => $row['name'], 'description' => $row['description'], 'points_required' => $row['points'], 'stock' => $row['stock'], 'image_path' => null, 'icon' => $row['icon'], 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
                $rewards[$row['code']] = DB::table('rewards')->where('code', $row['code'])->value('id');
            }

            DB::table('reward_redemptions')->updateOrInsert(
                ['reference_code' => 'RED-HIST'],
                [
                    'reward_id' => $rewards['RW-001'], 'resident_profile_id' => $residents['W-001'],
                    'points_cost' => 200, 'status' => 'completed', 'created_by' => $users['W-001'],
                    'handed_by' => $users['OP-001'], 'completed_at' => $ago(1), 'note' => null,
                    'created_at' => $ago(1), 'updated_at' => $now,
                ]
            );
            $redemptionId = DB::table('reward_redemptions')->where('reference_code', 'RED-HIST')->value('id');

            /* -------------------------------------------------------------
             * 5. Donatur, donasi uang, SupportPoints, pengeluaran, buku kas.
             * ----------------------------------------------------------- */
            $donorRows = [
                ['code' => 'DONOR-001', 'name' => 'Sahabat RW 13', 'type' => 'individual', 'alias' => 'Sahabat RW 13'],
                ['code' => 'DONOR-002', 'name' => 'Warga RW 13', 'type' => 'individual', 'alias' => 'Sahabat Lingkungan'],
            ];
            $donors = [];
            foreach ($donorRows as $row) {
                DB::table('donors')->updateOrInsert(
                    ['code' => $row['code']],
                    ['resident_profile_id' => null, 'name' => $row['name'], 'phone' => null, 'donor_type' => $row['type'], 'display_mode' => 'alias', 'public_alias' => $row['alias'], 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
                $donors[$row['code']] = DB::table('donors')->where('code', $row['code'])->value('id');
            }

            $donationRows = [
                ['ref' => 'DON-HIST-01', 'donor' => 'DONOR-001', 'name_snapshot' => 'Sahabat RW 13', 'alias' => 'Sahabat RW 13', 'amount' => 50000, 'purpose' => 'Kegiatan lingkungan', 'status' => 'verified', 'proof' => 'BUKTI-DON-001', 'points' => 50, 'days' => 7],
                ['ref' => 'DON-2602', 'donor' => 'DONOR-002', 'name_snapshot' => 'Warga RW 13', 'alias' => 'Sahabat Lingkungan', 'amount' => 100000, 'purpose' => 'Pengadaan perlengkapan pemilahan', 'status' => 'pending_receipt', 'proof' => null, 'points' => 0, 'days' => 0],
            ];
            $donations = [];
            foreach ($donationRows as $row) {
                $createdAt = $ago($row['days']);
                DB::table('donations')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'donor_id' => $donors[$row['donor']], 'donor_name_snapshot' => $row['name_snapshot'],
                        'display_mode_snapshot' => 'alias', 'alias_snapshot' => $row['alias'], 'amount' => $row['amount'],
                        'purpose' => $row['purpose'], 'status' => $row['status'], 'proof_reference' => $row['proof'],
                        'support_points_awarded' => $row['points'], 'created_by' => $users['BEN-001'],
                        // Prototype tidak menyimpan aktor verifikasi; kolom ini dibiarkan kosong.
                        'verified_by' => null, 'received_at' => $row['status'] === 'verified' ? $createdAt : null,
                        'note' => null, 'created_at' => $createdAt, 'updated_at' => $now,
                    ]
                );
                $donations[$row['ref']] = DB::table('donations')->where('reference_code', $row['ref'])->value('id');
            }

            DB::table('support_point_transactions')->updateOrInsert(
                ['reference_code' => 'SUP-PT-DON-HIST-01'],
                [
                    'donor_id' => $donors['DONOR-001'], 'points_delta' => 50, 'kind' => 'earn',
                    'description' => 'SupportPoints dari donasi uang terverifikasi',
                    'pointable_type' => 'App\\Models\\Donation', 'pointable_id' => $donations['DON-HIST-01'],
                    'created_by' => $users['BEN-001'], 'approved_by' => null,
                    'reason' => 'Poin diberikan setelah dana diterima.', 'created_at' => $ago(7), 'updated_at' => $now,
                ]
            );

            $outflowCreatedAt = $ago(10);
            DB::table('material_outflows')->updateOrInsert(
                ['reference_code' => 'OUT-HIST-01'],
                [
                    'material_id' => $materialIds['pet_bening'], 'weight_kg' => 8,
                    'destination' => 'pengepul', 'destination_name' => 'Mitra Daur Ulang Kiaracondong',
                    'amount_reported_rp' => 450000, 'amount_received_rp' => 450000,
                    'payment_status' => 'received', 'proof_reference' => 'BUKTI-OUT-001',
                    'payment_checked_by' => $users['BEN-001'], 'payment_received_at' => $outflowCreatedAt,
                    'status' => 'verified', 'created_by' => $users['OP-001'], 'verified_by' => $users['OP-001'],
                    'verified_at' => null, 'note' => null, 'created_at' => $outflowCreatedAt, 'updated_at' => $now,
                ]
            );
            $outflowId = DB::table('material_outflows')->where('reference_code', 'OUT-HIST-01')->value('id');

            $cashRows = [
                ['ref' => 'KAS-001', 'date_days' => 10, 'direction' => 'in', 'category' => 'Penjualan material ke pengepul', 'amount' => 450000, 'description' => 'Pembayaran dari mitra pengepul', 'proof' => 'BUKTI-OUT-001', 'source_type' => 'App\\Models\\MaterialOutflow', 'source_id' => $outflowId, 'creator' => 'BEN-001'],
                ['ref' => 'KAS-002', 'date_days' => 7, 'direction' => 'in', 'category' => 'Donasi uang', 'amount' => 50000, 'description' => 'Donasi terverifikasi — nama tampilan alias', 'proof' => 'BUKTI-DON-001', 'source_type' => 'App\\Models\\Donation', 'source_id' => $donations['DON-HIST-01'], 'creator' => 'BEN-001'],
                ['ref' => 'KAS-003', 'date_days' => 5, 'direction' => 'out', 'category' => 'Pembelian reward', 'amount' => 100000, 'description' => 'Pengadaan reward', 'proof' => 'BUKTI-EXP-001', 'source_type' => null, 'source_id' => null, 'creator' => 'BEN-001'],
                ['ref' => 'KAS-004', 'date_days' => 15, 'direction' => 'out', 'category' => 'Pembayaran setoran warga', 'amount' => 3750, 'description' => 'Pembayaran SET-2601', 'proof' => 'BUKTI-PAY-001', 'source_type' => 'App\\Models\\Payout', 'source_id' => $payouts['PAY-HIST-01'], 'creator' => 'BEN-001'],
            ];
            foreach ($cashRows as $row) {
                $date = $ago($row['date_days']);
                DB::table('cash_transactions')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'transaction_at' => $date, 'direction' => $row['direction'], 'category' => $row['category'],
                        'amount' => $row['amount'], 'description' => $row['description'], 'proof_reference' => $row['proof'],
                        'cashable_type' => $row['source_type'], 'cashable_id' => $row['source_id'],
                        'created_by' => $users[$row['creator']], 'posted_at' => $date,
                        'created_at' => $date, 'updated_at' => $now,
                    ]
                );
            }

            /* -------------------------------------------------------------
             * 6. Riwayat EcoPoints (termasuk histori yang tidak punya transaksi
             * sumber terpisah di prototype, sehingga memakai reference_code).
             * ----------------------------------------------------------- */
            $ledgerRows = [
                ['resident' => 'W-001', 'points' => 120, 'description' => 'Setoran PET bening 1,5 kg', 'ref' => 'SET-2601', 'days' => 15],
                ['resident' => 'W-001', 'points' => 150, 'description' => 'Donasi kardus 2 kg', 'ref' => 'SET-2602', 'days' => 14],
                ['resident' => 'W-001', 'points' => 60, 'description' => 'Setoran PET berwarna 1 kg', 'ref' => 'HIST-01', 'days' => 13],
                ['resident' => 'W-001', 'points' => 105, 'description' => 'Donasi galon 1 kg', 'ref' => 'HIST-02', 'days' => 12],
                ['resident' => 'W-001', 'points' => 120, 'description' => 'Setoran kaleng aluminium 1 kg', 'ref' => 'HIST-03', 'days' => 11],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Kehadiran kegiatan RW', 'ref' => 'EV-H1', 'days' => 13],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Kehadiran kegiatan RW', 'ref' => 'EV-H2', 'days' => 12],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Kehadiran kegiatan RW', 'ref' => 'EV-H3', 'days' => 11],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Kehadiran kegiatan RW', 'ref' => 'EV-H4', 'days' => 10],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Kehadiran kegiatan RW', 'ref' => 'EV-H5', 'days' => 9],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Berhasil menjual produk pemanfaatan', 'ref' => 'PROD-S1', 'days' => 7],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Berhasil menjual produk pemanfaatan', 'ref' => 'PROD-S2', 'days' => 6],
                ['resident' => 'W-001', 'points' => 50, 'description' => 'Berhasil menjual produk pemanfaatan', 'ref' => 'PROD-S3', 'days' => 5],
                ['resident' => 'W-001', 'points' => 30, 'description' => 'Membeli produk pemanfaatan', 'ref' => 'PROD-B1', 'days' => 5],
                ['resident' => 'W-001', 'points' => 30, 'description' => 'Membeli produk pemanfaatan', 'ref' => 'PROD-B2', 'days' => 4],
                ['resident' => 'W-001', 'points' => 75, 'description' => 'Donasi kardus 1 kg', 'ref' => 'HIST-04', 'days' => 4],
                ['resident' => 'W-001', 'points' => 30, 'description' => 'Setoran PET berwarna 0,5 kg', 'ref' => 'HIST-05', 'days' => 3],
                ['resident' => 'W-001', 'points' => 40, 'description' => 'Setoran PET bening 0,5 kg', 'ref' => 'HIST-06', 'days' => 2],
                ['resident' => 'W-001', 'points' => -200, 'description' => 'Penukaran reward: paket bibit', 'ref' => 'REW-HIST', 'days' => 1],
                ['resident' => 'W-002', 'points' => 820, 'description' => 'Akumulasi kontribusi terverifikasi sebelumnya', 'ref' => 'HIST-W002', 'days' => 20],
                ['resident' => 'W-003', 'points' => 530, 'description' => 'Akumulasi kontribusi terverifikasi sebelumnya', 'ref' => 'HIST-W003', 'days' => 30],
            ];
            foreach ($ledgerRows as $row) {
                $pointableType = null;
                $pointableId = null;
                if (isset($deposits[$row['ref']])) {
                    $pointableType = 'App\\Models\\Deposit';
                    $pointableId = $deposits[$row['ref']];
                } elseif ($row['ref'] === 'REW-HIST') {
                    $pointableType = 'App\\Models\\RewardRedemption';
                    $pointableId = $redemptionId;
                }
                DB::table('eco_point_transactions')->updateOrInsert(
                    ['reference_code' => $row['ref']],
                    [
                        'resident_profile_id' => $residents[$row['resident']],
                        'points_delta' => $row['points'],
                        'kind' => $row['points'] < 0 ? 'redeem' : 'earn',
                        'description' => $row['description'],
                        'pointable_type' => $pointableType,
                        'pointable_id' => $pointableId,
                        'created_by' => null,
                        'approved_by' => null,
                        'reason' => 'Riwayat awal dari data dummy prototype.',
                        'created_at' => $ago($row['days']),
                        'updated_at' => $now,
                    ]
                );
            }

            DB::table('audit_logs')->updateOrInsert(
                ['action' => 'Pemeriksaan audit log', 'user_id' => $users['ADM-001'], 'reason' => 'Riwayat pencatatan awal'],
                [
                    'role_name' => 'Superadmin', 'auditable_type' => null, 'auditable_id' => null,
                    'before_data' => json_encode(['object' => 'Riwayat awal'], JSON_UNESCAPED_UNICODE),
                    'after_data' => json_encode(['access' => 'Akses baca-saja'], JSON_UNESCAPED_UNICODE),
                    'ip_address' => null, 'user_agent' => null, 'created_at' => $ago(1),
                ]
            );

            $settings = [
                ['key' => 'rounding', 'group' => 'points', 'value' => 'nearest-half-up'],
                ['key' => 'leaderboardDefault', 'group' => 'privacy', 'value' => 'alias'],
                ['key' => 'priceEffectiveDate', 'group' => 'tariff', 'value' => '2026-10-30'],
                ['key' => 'priceOfficial', 'group' => 'tariff', 'value' => false],
                ['key' => 'rankThresholds', 'group' => 'points', 'value' => EcoRank::tiers()],
                ['key' => 'attendancePoints', 'group' => 'points', 'value' => 50],
                ['key' => 'productSalePoints', 'group' => 'points', 'value' => 50],
                ['key' => 'productPurchasePoints', 'group' => 'points', 'value' => 30],
                ['key' => 'supportPointsPerThousand', 'group' => 'points', 'value' => 1],
                ['key' => 'donation_channel', 'group' => 'donation', 'value' => [
                    'bank_name' => 'Bank BRI',
                    'account_number' => '0081 3245 6789 012',
                    'account_holder' => 'RW 13 Kelurahan Babakan Sari',
                    'qris_path' => 'images/donations/qris.png',
                    'contact_name' => 'Bu Lina — Bendahara',
                    'contact_phone' => '081234560031',
                    'sponsorship_note' => 'Dukungan sponsor dipakai untuk kerja bakti, perlengkapan pilah, dan kegiatan lingkungan RW 13. Hubungi bendahara untuk kesepakatan dan laporan penggunaan dana.',
                ]],
            ];
            foreach ($settings as $setting) {
                DB::table('app_settings')->updateOrInsert(
                    ['key' => $setting['key']],
                    ['group' => $setting['group'], 'value' => json_encode($setting['value']), 'updated_by' => $users['ADM-001'], 'created_at' => $now, 'updated_at' => $now]
                );
            }

        });
    }
}
