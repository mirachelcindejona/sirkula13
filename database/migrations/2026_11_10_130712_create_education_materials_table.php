<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_materials', function (Blueprint $table) {

            $table->id();
            $table->string('code', 50)->unique();
            $table->string('title', 180);
            $table->string('category', 100)->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->longText('content');
            $table->json('checklist')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_materials');
    }
};