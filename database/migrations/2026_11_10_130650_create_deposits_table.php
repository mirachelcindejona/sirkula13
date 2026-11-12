<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            $table->foreignId('resident_profile_id')->nullable()->constrained('resident_profiles')->restrictOnDelete();
            $table->string('external_name', 150)->nullable();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->foreignId('material_rate_id')->nullable()->constrained('material_rates')->nullOnDelete();
            $table->string('type', 20); // sale atau donation
            $table->decimal('weight_kg', 10, 3);
            // Snapshot menjaga nilai transaksi lama meski master tarif berubah.
            $table->decimal('price_per_kg_snapshot', 15, 2)->default(0);
            $table->date('price_effective_date_snapshot')->nullable();
            $table->boolean('price_official_at_creation')->default(false);
            $table->decimal('eco_points_per_kg_snapshot', 10, 2)->default(0);
            $table->decimal('donation_multiplier_snapshot', 4, 2)->default(1.50);
            $table->decimal('money_amount', 15, 2)->default(0);
            $table->unsignedInteger('eco_points_awarded')->default(0);
            $table->boolean('accepted')->default(true);
            $table->text('quality_note')->nullable();
            $table->boolean('resident_choice_confirmed')->default(false);
            $table->string('status', 30)->default('draft')->index();
            $table->string('source', 20)->default('online');
            $table->string('offline_transaction_id', 100)->nullable()->unique();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('correction_reason')->nullable();
            $table->timestamps();
            $table->index(['resident_profile_id', 'created_at'], 'deposits_resident_date_idx');
            $table->index(['material_id', 'status'], 'deposits_material_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};