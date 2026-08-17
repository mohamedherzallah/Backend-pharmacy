<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `AuthController::verifyOtp()` calls $user->update(['is_verified' => true]),
     * but no `is_verified` column existed on the users table, so verifying an
     * OTP crashed with a SQL "unknown column" error. This adds the column.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }
};
