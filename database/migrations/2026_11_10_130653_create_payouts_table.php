<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            // Model prototype: satu setoran jual memiliki paling banyak satu pencairan.
            $table->foreignId('deposit_id')->unique()->constrained('deposits')->restrictOnDelete();
            $table->foreignId('resident_profile_id')->constrained('resident_profiles')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('awaiting_approval')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('proof_reference', 150)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};