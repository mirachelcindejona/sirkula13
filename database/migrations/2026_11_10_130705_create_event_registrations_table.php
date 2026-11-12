<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('resident_profile_id')->constrained('resident_profiles')->restrictOnDelete();
            $table->string('status', 30)->default('registered');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'resident_profile_id'], 'event_registration_once_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};