<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            $table->string('category', 80)->nullable();
            $table->string('title', 180);
            $table->date('event_date');
            $table->string('event_time', 80)->nullable();
            $table->string('location', 180);
            $table->unsignedInteger('quota')->nullable();
            $table->text('description')->nullable();
            $table->string('poster_path')->nullable();
            $table->unsignedSmallInteger('attendance_points')->default(50);
            $table->unsignedSmallInteger('volunteer_points')->default(50);
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'event_date'], 'events_public_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};