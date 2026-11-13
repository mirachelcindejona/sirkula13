<?php

namespace App\Support;

class CashCategories
{
    /**
     * @return array<int, string>
     */
    public static function incoming(): array
    {
        return [
            'Penjualan material ke pengepul',
            'Penjualan produk',
            'Sponsor / dukungan mitra',
            'Donasi uang',
            'Koreksi pencairan',
            'Pemasukan lain',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function outgoing(): array
    {
        return [
            'Biaya operasional',
            'Pembelian reward',
            'Pencairan setoran warga',
            'Pembayaran setoran warga',
            'Pengeluaran lain',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function for(?string $direction): array
    {
        return $direction === 'out' ? self::outgoing() : self::incoming();
    }
}
