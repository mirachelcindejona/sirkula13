<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batch_rows', function (Blueprint $table) {

            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('offline_transaction_id', 100)->nullable()->unique();
            $table->dateTime('occurred_at')->nullable();
            $table->string('resident_code', 40)->nullable();
            $table->string('activity_type', 30)->nullable();
            $table->string('material_code', 50)->nullable();
            $table->decimal('weight_kg', 10, 3)->nullable();
            $table->decimal('value_rp', 15, 2)->nullable();
            $table->json('raw_data')->nullable();
            $table->json('validation_errors')->nullable();
            $table->string('status', 30)->default('pending_review')->index();
            $table->foreignId('deposit_id')->nullable()->unique()->constrained('deposits')->nullOnDelete();
            $table->timestamps();
            $table->unique(['import_batch_id', 'row_number'], 'import_batch_row_number_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batch_rows');
    }
};