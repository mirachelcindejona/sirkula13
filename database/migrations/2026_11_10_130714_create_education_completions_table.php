<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_completions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('education_material_id')->constrained('education_materials')->restrictOnDelete();
            $table->foreignId('resident_profile_id')->constrained('resident_profiles')->restrictOnDelete();
            $table->unsignedSmallInteger('points_awarded')->default(30);
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();
            $table->unique(
                ['education_material_id', 'resident_profile_id'],
                'education_completion_once_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_completions');
    }
};