<?php

namespace App\Support;

use App\Models\Deposit;
use App\Models\Payout;
use App\Models\ProductOrder;
use App\Models\User;

class Navigation
{
    /**
     * Menu mengikuti revisi akhir prototipe. Materi edukasi tidak ditampilkan.
     *
     * @return array{groups: array<int, array<string, mixed>>, bottom: array<int, array<string, mixed>>}
     */
    public static function for(User $user): array
    {
        $groups = [];
        $add = function (string $title, array $items, array $options = []) use (&$groups, $user): void {
            $visible = array_values(array_filter($items, fn (array $item) => self::allowed($user, $item['route'])));
            if ($visible !== []) {
                $groups[] = array_merge(['title' => $title, 'items' => $visible], $options);
            }
        };

        if ($user->hasRole('superadmin')) {
            $add('Ringkasan', [['route' => 'admin.dashboard', 'label' => 'Ringkasan admin', 'icon' => 'dashboard']], ['standalone' => true]);
            $add('Akun & warga', [
                ['route' => 'admin.approvals', 'label' => 'Persetujuan akun', 'icon' => 'check'],
                ['route' => 'admin.roles', 'label' => 'Hak akses petugas', 'icon' => 'shield'],
                ['route' => 'bank.residents', 'label' => 'Data warga', 'icon' => 'user'],
            ], ['icon' => 'user']);
            $add('Operator', self::operatorItems(), ['icon' => 'scale']);
            $add('Pengelola Kegiatan', self::programItems(true), ['icon' => 'calendar']);
            $add('Bendahara', self::financeItems(true), ['icon' => 'wallet']);
            $add('Pengaturan & keamanan', [
                ['route' => 'admin.tariff', 'label' => 'Tarif & aturan poin', 'icon' => 'settings'],
                ['route' => 'admin.materials.index', 'label' => 'Jenis material', 'icon' => 'box'],
                ['route' => 'admin.rewards.index', 'label' => 'Katalog reward', 'icon' => 'gift'],
                ['route' => 'admin.fraud', 'label' => 'Transaksi berisiko', 'icon' => 'alert'],
                ['route' => 'admin.audit', 'label' => 'Riwayat aktivitas', 'icon' => 'history'],
            ], ['icon' => 'shield']);
            $add('Layanan publik', self::publicItems(true, true), ['icon' => 'home']);
        } else {
            if ($user->hasRole('warga')) {
                $add('Ringkasan', [['route' => 'app.home', 'label' => 'Beranda saya', 'icon' => 'dashboard']], ['standalone' => true]);
                $add('Akun warga', [
                    ['route' => 'citizen.profile', 'label' => 'Profil & ID warga', 'icon' => 'user'],
                    ['route' => 'citizen.deposits', 'label' => 'Riwayat setoran', 'icon' => 'scale'],
                    ['route' => 'citizen.points', 'label' => 'EcoPoints & reward', 'icon' => 'sparkles'],
                    ['route' => 'citizen.products', 'label' => 'Produk saya', 'icon' => 'box'],
                    ['route' => 'citizen.orders', 'label' => 'Pesanan saya', 'icon' => 'receipt'],
                ], ['icon' => 'user']);
            }
            if ($user->hasRole('operator-bank-sampah')) {
                $add('Ringkasan', [['route' => 'bank.dashboard', 'label' => 'Ringkasan operator', 'icon' => 'dashboard']], ['standalone' => true]);
                $add('Operator', [
                    ['route' => 'bank.deposits.create', 'label' => 'Catat setoran', 'icon' => 'scale'],
                    ['route' => 'bank.records', 'label' => 'Catatan setoran', 'icon' => 'receipt'],
                    ['route' => 'bank.inventory', 'label' => 'Stok & penyaluran', 'icon' => 'box'],
                    ['route' => 'bank.residents', 'label' => 'Cari data warga', 'icon' => 'user'],
                    ['route' => 'bank.handover', 'label' => 'Serah terima reward', 'icon' => 'gift'],
                    ['route' => 'finance.payouts', 'label' => 'Pencairan setoran', 'icon' => 'wallet'],
                ], ['icon' => 'scale']);
            }
            if ($user->hasRole('pengelola-kegiatan')) {
                $add('Ringkasan', [['route' => 'program.dashboard', 'label' => 'Ringkasan program', 'icon' => 'dashboard']], ['standalone' => true]);
                $add('Pengelola Kegiatan', self::programItems(false), ['icon' => 'calendar']);
            }
            if ($user->hasRole('bendahara')) {
                $add('Ringkasan', [['route' => 'finance.dashboard', 'label' => 'Ringkasan bendahara', 'icon' => 'dashboard']], ['standalone' => true]);
                $add('Bendahara', self::financeItems(false), ['icon' => 'wallet']);
            }
            $add('Layanan umum', self::publicItems(false, $user->hasRole('warga')), ['icon' => $user->hasRole('warga') ? 'home' : 'book']);
        }

        return [
            'groups' => self::withCounts($groups),
            'bottom' => self::bottom($user),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    private static function withCounts(array $groups): array
    {
        $counts = [
            'admin.approvals' => User::query()->whereIn('status', ['pending', 'needs_info'])->count(),
            'bank.records' => Deposit::query()->whereDate('created_at', today())->count(),
            'finance.payouts' => Payout::query()->where('status', '!=', 'paid')->count(),
            'admin.fraud' => ProductOrder::query()->where('status', 'held_for_review')->count(),
        ];

        foreach ($groups as &$group) {
            foreach ($group['items'] as &$item) {
                $item['count'] = $counts[$item['route']] ?? 0;
            }
        }

        return $groups;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function operatorItems(): array
    {
        return [
            ['route' => 'bank.dashboard', 'label' => 'Ringkasan operator', 'icon' => 'dashboard'],
            ['route' => 'bank.deposits.create', 'label' => 'Catat setoran', 'icon' => 'scale'],
            ['route' => 'bank.records', 'label' => 'Catatan setoran', 'icon' => 'receipt'],
            ['route' => 'bank.inventory', 'label' => 'Stok & penyaluran', 'icon' => 'box'],
            ['route' => 'bank.handover', 'label' => 'Serah terima reward', 'icon' => 'gift'],
            ['route' => 'finance.payouts', 'label' => 'Pencairan setoran', 'icon' => 'wallet'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function programItems(bool $forAdmin): array
    {
        $items = [
            ['route' => 'program.events', 'label' => 'Kegiatan & kehadiran', 'icon' => 'calendar'],
            ['route' => 'program.catalog', 'label' => 'Produk pengurus', 'icon' => 'box'],
            ['route' => 'program.orders', 'label' => 'Pantau pesanan', 'icon' => 'receipt'],
        ];
        if ($forAdmin) {
            array_unshift($items, ['route' => 'program.dashboard', 'label' => 'Ringkasan program', 'icon' => 'dashboard']);
        }

        return $items;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function financeItems(bool $includeApproval): array
    {
        $items = [
            ['route' => 'finance.dashboard', 'label' => 'Ringkasan keuangan', 'icon' => 'dashboard'],
            ['route' => 'finance.payouts', 'label' => 'Pencairan setoran', 'icon' => 'wallet'],
            ['route' => 'finance.cashbook', 'label' => 'Buku kas', 'icon' => 'receipt'],
            ['route' => 'finance.donations', 'label' => 'Verifikasi donasi', 'icon' => 'heart'],
            ['route' => 'finance.channel', 'label' => 'Rekening & QRIS', 'icon' => 'heart'],
            ['route' => 'finance.reports', 'label' => $includeApproval ? 'Laporan' : 'Laporan keuangan', 'icon' => 'chart'],
        ];

        return $items;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function publicItems(bool $includePublicHome, bool $includeCitizenLinks): array
    {
        $items = [
            ['route' => 'public.guide', 'label' => 'Panduan pemilahan', 'icon' => 'book'],
            ['route' => 'public.events', 'label' => 'Kegiatan warga', 'icon' => 'calendar'],
            ['route' => 'public.catalog', 'label' => 'Katalog produk', 'icon' => 'box'],
        ];
        if ($includePublicHome) {
            array_unshift($items, ['route' => 'app.home', 'label' => 'Beranda publik', 'icon' => 'home']);
        }
        if ($includeCitizenLinks || $includePublicHome) {
            $items[] = ['route' => 'public.leaderboard', 'label' => 'Peringkat warga', 'icon' => 'award'];
            $items[] = ['route' => 'public.donation', 'label' => 'Donasi & dukungan', 'icon' => 'heart'];
        }

        return $items;
    }

    private static function allowed(User $user, string $route): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        $map = [
            'app.home' => ['warga'],
            'citizen.profile' => ['warga'],
            'citizen.deposits' => ['warga'],
            'citizen.points' => ['warga'],
            'citizen.orders' => ['warga'],
            'citizen.products' => ['warga'],
            'bank.dashboard' => ['operator-bank-sampah'],
            'bank.deposits.create' => ['operator-bank-sampah'],
            'bank.records' => ['operator-bank-sampah'],
            'bank.inventory' => ['operator-bank-sampah'],
            'bank.residents' => ['operator-bank-sampah'],
            'bank.handover' => ['operator-bank-sampah'],
            'program.dashboard' => ['pengelola-kegiatan'],
            'program.events' => ['pengelola-kegiatan'],
            'program.catalog' => ['pengelola-kegiatan'],
            'program.orders' => ['pengelola-kegiatan'],
            'finance.dashboard' => ['bendahara'],
            'finance.payouts' => ['operator-bank-sampah', 'bendahara'],
            'finance.cashbook' => ['bendahara'],
            'finance.donations' => ['bendahara'],
            'finance.channel' => ['bendahara'],
            'finance.reports' => ['bendahara'],
        ];

        if (str_starts_with($route, 'public.')) {
            return true;
        }

        return isset($map[$route]) && $user->hasRole(...$map[$route]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function bottom(User $user): array
    {
        $role = $user->primaryRoleSlug();
        $items = match ($role) {
            'warga' => [
                ['route' => 'app.home', 'label' => 'Beranda', 'icon' => 'home'],
                ['route' => 'citizen.deposits', 'label' => 'Setoran', 'icon' => 'scale'],
                ['route' => 'public.events', 'label' => 'Kegiatan', 'icon' => 'calendar'],
                ['route' => 'citizen.points', 'label' => 'EcoPoints', 'icon' => 'sparkles'],
            ],
            'operator-bank-sampah' => [
                ['route' => 'bank.dashboard', 'label' => 'Beranda', 'icon' => 'home'],
                ['route' => 'bank.deposits.create', 'label' => 'Setoran', 'icon' => 'scale'],
                ['route' => 'bank.inventory', 'label' => 'Stok', 'icon' => 'box'],
                ['route' => 'finance.payouts', 'label' => 'Cairkan', 'icon' => 'wallet'],
            ],
            'pengelola-kegiatan' => [
                ['route' => 'program.dashboard', 'label' => 'Beranda', 'icon' => 'home'],
                ['route' => 'program.events', 'label' => 'Program', 'icon' => 'calendar'],
                ['route' => 'program.catalog', 'label' => 'Produk', 'icon' => 'box'],
                ['route' => 'program.orders', 'label' => 'Pesanan', 'icon' => 'receipt'],
            ],
            'bendahara' => [
                ['route' => 'finance.dashboard', 'label' => 'Beranda', 'icon' => 'home'],
                ['route' => 'finance.payouts', 'label' => 'Cairkan', 'icon' => 'wallet'],
                ['route' => 'finance.cashbook', 'label' => 'Buku kas', 'icon' => 'receipt'],
                ['route' => 'finance.reports', 'label' => 'Laporan', 'icon' => 'chart'],
            ],
            'superadmin' => [
                ['route' => 'admin.dashboard', 'label' => 'Beranda', 'icon' => 'home'],
                ['route' => 'admin.approvals', 'label' => 'Akun', 'icon' => 'check'],
                ['route' => 'admin.tariff', 'label' => 'Tarif', 'icon' => 'settings'],
                ['route' => 'admin.audit', 'label' => 'Audit', 'icon' => 'history'],
            ],
            default => [
                ['route' => 'app.home', 'label' => 'Beranda', 'icon' => 'home'],
            ],
        };
        $items[] = ['route' => $items[0]['route'], 'label' => 'Menu', 'icon' => 'menu', 'drawer' => true];

        return $items;
    }
}
