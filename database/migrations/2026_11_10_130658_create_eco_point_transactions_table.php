<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eco_point_transactions', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 80)->nullable()->unique();
            $table->foreignId('resident_profile_id')->constrained('resident_profiles')->restrictOnDelete();
            $table->integer('points_delta'); // Negatif untuk redeem.
            $table->string('kind', 20); // earn, redeem, adjustment
            $table->string('description', 255);
            // pointable adalah relasi polymorphic; tidak membuat FK ke banyak tabel sumber.
            $table->nullableMorphs('pointable');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(
                ['resident_profile_id', 'pointable_type', 'pointable_id', 'kind'],
                'eco_points_source_unique'
            );
            $table->index(['resident_profile_id', 'created_at'], 'eco_points_resident_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eco_point_transactions');
    }
};