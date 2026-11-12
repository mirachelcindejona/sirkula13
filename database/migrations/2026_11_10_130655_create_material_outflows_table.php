<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_outflows', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->decimal('weight_kg', 10, 3);
            $table->string('destination', 100);
            $table->string('destination_name', 150)->nullable();
            $table->decimal('amount_reported_rp', 15, 2)->default(0);
            $table->decimal('amount_received_rp', 15, 2)->nullable();
            $table->string('payment_status', 30)->default('not_received');
            $table->string('proof_reference', 150)->nullable();
            $table->foreignId('payment_checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payment_received_at')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['material_id', 'status'], 'material_outflow_stock_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_outflows');
    }
};