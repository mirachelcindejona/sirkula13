<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('readiness_checks', function (Blueprint $table) {

            $table->id();
            // Satu item kesiapan memiliki satu status pemeriksaan terkini.
            $table->foreignId('readiness_item_id')->unique()->constrained('readiness_items')->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readiness_checks');
    }
};