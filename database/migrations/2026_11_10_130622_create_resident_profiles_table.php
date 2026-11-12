<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_profiles', function (Blueprint $table) {

            $table->id();
            // Profil dapat dibuat operator sebelum warga mempunyai akun.
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            // Warga berstatus pending belum mempunyai kode warga.
            $table->string('resident_code', 40)->nullable()->unique();
            $table->string('full_name', 150);
            $table->string('phone', 25)->nullable()->index();
            $table->string('rt', 10)->nullable()->index();
            $table->string('house_number', 30)->nullable();
            $table->string('household_name', 150)->nullable();
            $table->string('display_name', 100)->nullable();
            $table->boolean('leaderboard_visible')->default(true);
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_profiles');
    }
};