<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {

            $table->id();
            $table->string('code', 50)->unique();
            // Donatur dapat berupa warga terdaftar atau pihak luar RW.
            $table->foreignId('resident_profile_id')->nullable()->unique()->constrained('resident_profiles')->nullOnDelete();
            $table->string('name', 180);
            $table->string('phone', 25)->nullable();
            $table->string('donor_type', 30)->default('individual');
            $table->string('display_mode', 30)->default('alias');
            $table->string('public_alias', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donors');
    }
};