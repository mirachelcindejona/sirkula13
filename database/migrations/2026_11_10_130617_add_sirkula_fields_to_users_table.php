<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Login utama memakai nomor HP. Email tetap tersedia tetapi opsional.
            $table->string('email')->nullable()->change();
            $table->string('user_code', 40)->nullable()->unique();
            $table->string('phone', 25)->nullable()->unique();
            $table->string('status', 30)->default('active');
            $table->boolean('login_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_user_code_unique');
            $table->dropUnique('users_phone_unique');
            $table->dropColumn(['user_code', 'phone', 'status', 'login_enabled']);
            // email tidak dibuat NOT NULL lagi agar data tidak hilang saat rollback.
        });
    }
};