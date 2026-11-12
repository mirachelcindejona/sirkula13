<?php

namespace App\Support;

class Labels
{
    public static function status(?string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'pending' => 'Menunggu',
            'pending_pickup' => 'Menunggu pengambilan',
            'pending_receipt' => 'Menunggu penerimaan',
            'needs_info' => 'Perlu data',
            'submitted' => 'Menunggu verifikasi',
            'recorded' => 'Tercatat',
            'draft' => 'Draft',
            'verified' => 'Terverifikasi',
            'approved' => 'Disetujui',
            'published' => 'Tayang',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
            'paid' => 'Lunas',
            'awaiting_approval' => 'Menunggu persetujuan',
            'awaiting_seller' => 'Menunggu konfirmasi penjual',
            'attended' => 'Hadir',
            'fraud_flag' => 'Perlu ditinjau',
            'uploaded' => 'Diunggah',
            'sale' => 'Jual',
            'donation' => 'Donasi',
            'registered' => 'Terdaftar',
            'review' => 'Menunggu review',
            'needs_correction' => 'Perlu koreksi',
            'held_for_review' => 'Ditahan untuk pemeriksaan',
            'awaiting_payment' => 'Menunggu pembayaran',
            default => $status ? str_replace('_', ' ', $status) : '—',
        };
    }

    public static function badge(?string $status): string
    {
        return match ($status) {
            'active', 'verified', 'approved', 'published', 'completed', 'paid', 'attended' => 'success',
            'pending', 'pending_pickup', 'pending_receipt', 'submitted', 'recorded', 'awaiting_approval', 'awaiting_seller', 'uploaded' => 'pending',
            'needs_info', 'fraud_flag', 'needs_correction', 'held_for_review' => 'warning',
            'rejected' => 'danger',
            'cancelled' => 'neutral',
            'donation' => 'donation',
            'sale' => 'info',
            'registered' => 'success',
            'review' => 'pending',
            'draft', 'inactive' => 'neutral',
            default => 'neutral',
        };
    }

    public static function money(float|int|string|null $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }

    public static function kg(float|int|string|null $weight): string
    {
        return rtrim(rtrim(number_format((float) $weight, 3, ',', '.'), '0'), ',').' kg';
    }
}
