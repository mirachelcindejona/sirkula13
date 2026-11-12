<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_orders', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('buyer_profile_id')->nullable()->constrained('resident_profiles')->nullOnDelete();
            $table->foreignId('seller_profile_id')->nullable()->constrained('resident_profiles')->nullOnDelete();
            $table->string('external_buyer_name', 150)->nullable();
            $table->string('external_buyer_phone', 25)->nullable();
            $table->boolean('is_external_buyer')->default(false);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price_snapshot', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('payment_method', 80)->nullable();
            $table->unsignedInteger('points_used')->default(0);
            $table->string('status', 30)->default('awaiting_seller')->index();
            $table->boolean('seller_confirmed')->default(false);
            $table->boolean('buyer_confirmed')->default(false);
            $table->timestamp('seller_confirmed_at')->nullable();
            $table->timestamp('buyer_confirmed_at')->nullable();
            $table->string('handover_proof', 180)->nullable();
            $table->text('held_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['buyer_profile_id', 'created_at'], 'product_order_buyer_date_idx');
            $table->index(['seller_profile_id', 'created_at'], 'product_order_seller_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_orders');
    }
};