<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 50)->unique();
            $table->foreignId('donor_id')->constrained('donors')->restrictOnDelete();
            $table->string('donor_name_snapshot', 180);
            $table->string('display_mode_snapshot', 30);
            $table->string('alias_snapshot', 120)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('purpose', 180);
            $table->string('status', 30)->default('pending_receipt')->index();
            $table->string('proof_reference', 180)->nullable();
            $table->unsignedInteger('support_points_awarded')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['donor_id', 'received_at'], 'donations_donor_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};