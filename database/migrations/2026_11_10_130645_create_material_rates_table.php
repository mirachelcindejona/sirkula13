<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_rates', function (Blueprint $table) {

            $table->id();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->decimal('buy_price_per_kg', 15, 2)->nullable();
            $table->decimal('eco_points_per_kg', 10, 2);
            $table->decimal('donation_multiplier', 4, 2)->default(1.50);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(['material_id', 'effective_from'], 'material_rate_material_from_unique');
            $table->index(['material_id', 'status', 'effective_from'], 'material_rate_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_rates');
    }
};