<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deposits')) {
            DB::table('deposits')->where('reference_code', 'SET-2606')->update([
                'accepted' => false,
                'money_amount' => 0,
                'eco_points_awarded' => 0,
                'quality_note' => 'Material tidak sesuai standar penerimaan.',
                'status' => 'verified',
                'correction_reason' => null,
                'verified_at' => DB::raw('COALESCE(verified_at, updated_at)'),
            ]);

            DB::table('deposits')
                ->whereIn('status', ['pending_verification', 'submitted', 'needs_correction', 'draft'])
                ->update([
                    'status' => 'verified',
                    'correction_reason' => null,
                    'verified_at' => DB::raw('COALESCE(verified_at, updated_at)'),
                ]);
        }

        if (Schema::hasTable('payouts')) {
            DB::table('payouts')->whereIn('status', ['awaiting_approval', 'approved'])->update(['status' => 'saved']);
            DB::table('payouts')->where('status', 'paid')->where('paid_amount', 0)->update([
                'paid_amount' => DB::raw('amount'),
            ]);
        }

        if (Schema::hasTable('material_outflows')) {
            DB::table('material_outflows')->where('status', 'completed')->update(['status' => 'verified']);
            DB::table('material_outflows')->where('destination', 'Pengepul')->update(['destination' => 'pengepul']);
        }

        if (Schema::hasTable('cash_transactions')) {
            DB::table('cash_transactions')->where('cashable_type', 'App\\Models\\Expense')->update([
                'cashable_type' => null,
                'cashable_id' => null,
            ]);
        }

        if (Schema::hasTable('eco_point_transactions')) {
            DB::table('eco_point_transactions')->where('reference_code', 'like', 'EDU-%')->delete();
            DB::table('eco_point_transactions')->where('reference_code', 'like', 'TASK-%')->delete();
        }

        if (Schema::hasTable('app_settings')) {
            DB::table('app_settings')->whereIn('key', [
                'educationPoints',
                'volunteerPoints',
                'backupCadence',
                'reservedOfflineIds',
            ])->delete();
        }

        Schema::dropIfExists('education_completions');
        Schema::dropIfExists('education_materials');
        Schema::dropIfExists('import_batch_rows');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('readiness_checks');
        Schema::dropIfExists('readiness_items');
        Schema::dropIfExists('volunteer_tasks');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('expenses');
    }

    public function down(): void
    {
        // Tabel yang dihapus tidak lagi dipakai aplikasi.
    }
};
