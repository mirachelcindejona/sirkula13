<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('unit', 20)->default('kg')->after('icon');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->string('unit_snapshot', 20)->nullable()->after('weight_kg');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('amount');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->timestamp('reversed_at')->nullable()->after('posted_at');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropUnique('cash_transactions_source_unique');
            $table->index(['cashable_type', 'cashable_id', 'direction'], 'cash_transactions_source_idx');
        });

        DB::table('deposits')->whereNull('unit_snapshot')->update(['unit_snapshot' => 'kg']);
        DB::table('payouts')->where('status', 'paid')->update(['paid_amount' => DB::raw('amount')]);
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropIndex('cash_transactions_source_idx');
            $table->unique(['cashable_type', 'cashable_id', 'direction'], 'cash_transactions_source_unique');
            $table->dropColumn('reversed_at');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('unit_snapshot');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
