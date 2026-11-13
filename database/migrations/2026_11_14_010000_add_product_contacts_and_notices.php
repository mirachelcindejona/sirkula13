<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('owner_kind', 20)->default('rw');
            $table->string('contact_name', 150)->nullable();
            $table->string('contact_phone', 30)->nullable();
        });

        DB::table('products')->whereNotNull('seller_profile_id')->update(['owner_kind' => 'warga']);

        $profiles = DB::table('resident_profiles')->get(['id', 'full_name', 'phone']);
        foreach ($profiles as $profile) {
            DB::table('products')
                ->where('seller_profile_id', $profile->id)
                ->where(function ($query): void {
                    $query->whereNull('contact_phone')->orWhere('contact_phone', '');
                })
                ->update([
                    'contact_name' => $profile->full_name,
                    'contact_phone' => $profile->phone,
                ]);
        }

        $sold = DB::table('product_orders')
            ->select('product_id', DB::raw('SUM(quantity) as qty'))
            ->where('status', 'completed')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        foreach ($sold as $productId => $qty) {
            $current = (int) DB::table('products')->where('id', $productId)->value('stock');
            DB::table('products')->where('id', $productId)->update([
                'stock' => max(0, $current - (int) $qty),
            ]);
        }

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 160);
            $table->string('body', 500);
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['owner_kind', 'contact_name', 'contact_phone']);
        });
    }
};
