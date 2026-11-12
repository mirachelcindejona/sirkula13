<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            $table->dateTime('transaction_at');
            $table->string('direction', 10); // in atau out
            $table->string('category', 100);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('proof_reference', 180)->nullable();
            // cashable adalah polymorphic source, bukan FK ke satu tabel tertentu.
            $table->nullableMorphs('cashable');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['cashable_type', 'cashable_id', 'direction'],
                'cash_transactions_source_unique'
            );
            $table->index(['direction', 'transaction_at'], 'cash_transactions_report_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};