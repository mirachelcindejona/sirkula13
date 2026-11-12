<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out']); // 'in' (penambahan), 'out' (penggunaan)
            $table->integer('points'); // Jumlah poin yang terlibat
            $table->integer('xp_gained')->default(0); // XP yang didapat (hanya bertambah pada jenis 'in')
            $table->string('activity_type'); // e.g., 'setor_sampah', 'penukaran_reward', 'bonus'
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
