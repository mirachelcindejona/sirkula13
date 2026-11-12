<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('image_path')->nullable();
            $table->string('image_key', 80)->nullable(); // ilustrasi pot/bag/decor dari prototype
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('seller_profile_id')->nullable()->constrained('resident_profiles')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'name'], 'products_public_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};