<?php

namespace App\Support;

use App\Exceptions\UserMessage;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ResidentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderFlow
{
    public static function place(Product $product, ResidentProfile $buyer, User $actor): ProductOrder
    {
        return DB::transaction(function () use ($product, $buyer, $actor): ProductOrder {
            $locked = Product::query()->with(['seller.user', 'creator'])->whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'published') {
                throw new UserMessage('Produk ini belum bisa dipesan.');
            }

            if ((int) $locked->seller_profile_id === (int) $buyer->id) {
                throw new UserMessage('Anda tidak bisa memesan produk sendiri.');
            }

            $phone = $locked->contact_phone ?: $locked->seller?->phone;
            if (! WhatsApp::digits($phone)) {
                throw new UserMessage('Penjual belum mencantumkan nomor WhatsApp. Pesanan belum dibuat.');
            }

            $reserved = (int) ProductOrder::query()
                ->where('product_id', $locked->id)
                ->whereNotIn('status', ['cancelled', 'declined', 'completed'])
                ->sum('quantity');

            if ((int) $locked->stock - $reserved < 1) {
                throw new UserMessage('Stok produk ini baru saja habis. Silakan coba lagi nanti.');
            }

            $order = new ProductOrder;
            $order->forceFill([
                'reference_code' => 'ORD-'.now()->format('ymdHis').random_int(10, 99),
                'product_id' => $locked->id,
                'buyer_profile_id' => $buyer->id,
                'seller_profile_id' => $locked->seller_profile_id,
                'quantity' => 1,
                'unit_price_snapshot' => $locked->price,
                'total_amount' => $locked->price,
                'status' => 'awaiting_seller',
                'created_by' => $actor->id,
            ])->save();

            $seller = $locked->owner_kind === 'warga' ? $locked->seller?->user : $locked->creator;
            if ($seller && $seller->id !== $actor->id) {
                Notices::send(
                    $seller,
                    'Ada pesanan baru',
                    $buyer->full_name.' memesan '.$locked->name.' ('.$order->reference_code.'). Tandai setelah barang diterima.',
                    $locked->owner_kind === 'warga' ? route('citizen.products') : route('program.orders')
                );
            }

            $order->setRelation('product', $locked);

            return $order;
        });
    }

    public static function link(ProductOrder $order): ?string
    {
        $order->loadMissing(['product.seller', 'buyer']);
        $product = $order->product;
        $buyer = $order->buyer?->full_name ?: 'warga RW 13';
        $message = "Halo, saya {$buyer} dari SIRKULA RW 13.\n"
            .'Saya ingin memesan '.($product?->name ?: 'produk').' (kode produk '.($product?->code ?: '-').").\n"
            ."Kode pesanan: {$order->reference_code}.\n"
            .'Apakah barangnya masih ada?';

        return WhatsApp::link($product?->contact_phone ?: $product?->seller?->phone, $message);
    }

    public static function complete(ProductOrder $order): void
    {
        DB::transaction(function () use ($order): void {
            $locked = ProductOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, ['awaiting_seller', 'awaiting_buyer'], true)) {
                throw new UserMessage('Pesanan ini sudah ditutup.');
            }

            $product = Product::query()->whereKey($locked->product_id)->lockForUpdate()->firstOrFail();
            $product->forceFill([
                'stock' => max(0, (int) $product->stock - (int) $locked->quantity),
            ])->save();

            $locked->forceFill([
                'status' => 'completed',
                'seller_confirmed' => true,
                'seller_confirmed_at' => now(),
                'buyer_confirmed' => true,
                'buyer_confirmed_at' => now(),
                'completed_at' => now(),
                'verified_at' => now(),
            ])->save();

            Notices::toProfile(
                $locked->buyer,
                'Pesanan selesai',
                'Pesanan '.$locked->reference_code.' untuk '.$product->name.' ditandai sudah diterima.',
                route('citizen.orders')
            );
        });
    }

    public static function cancel(ProductOrder $order, User $actor, string $reason): void
    {
        DB::transaction(function () use ($order, $actor, $reason): void {
            $locked = ProductOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, ['awaiting_seller', 'awaiting_buyer'], true)) {
                throw new UserMessage($locked->status === 'held_for_review'
                    ? 'Pesanan ini sedang diperiksa pengurus.'
                    : 'Pesanan ini sudah ditutup.');
            }

            $locked->load(['product.creator', 'product.seller.user', 'buyer.user', 'seller.user']);
            $locked->forceFill([
                'status' => 'cancelled',
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ])->save();

            $product = $locked->product;
            $buyerUser = $locked->buyer?->user;
            $sellerUser = $product?->owner_kind === 'warga' ? $locked->seller?->user : $product?->creator;
            $body = 'Pesanan '.$locked->reference_code.' dibatalkan. '.$reason;

            if ($buyerUser && $buyerUser->id !== $actor->id) {
                Notices::send($buyerUser, 'Pesanan dibatalkan', $body, route('citizen.orders'));
            }
            if ($sellerUser && $sellerUser->id !== $actor->id && $sellerUser->id !== $buyerUser?->id) {
                Notices::send(
                    $sellerUser,
                    'Pesanan dibatalkan',
                    $body,
                    $product?->owner_kind === 'warga' ? route('citizen.products') : route('program.orders')
                );
            }
        });
    }
}
