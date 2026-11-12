<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_point_transactions', function (Blueprint $table) {

            $table->id();
            $table->string('reference_code', 80)->nullable()->unique();
            $table->foreignId('donor_id')->constrained('donors')->restrictOnDelete();
            $table->integer('points_delta');
            $table->string('kind', 20)->default('earn');
            $table->string('description', 255)->nullable();
            $table->nullableMorphs('pointable');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(
                ['donor_id', 'pointable_type', 'pointable_id', 'kind'],
                'support_points_source_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_point_transactions');
    }
};