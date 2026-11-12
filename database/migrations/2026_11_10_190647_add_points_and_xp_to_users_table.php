<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        $table->integer('ecopoints')->default(0)->after('email');
        $table->integer('total_xp')->default(0)->after('ecopoints');
        $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete()->after('total_xp');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
