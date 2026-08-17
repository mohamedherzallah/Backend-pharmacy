<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The frontend (OrdersPage stats, orderAPI.cancel) and OrderController::update
     * both assume an order can be "cancelled", but the original enum only allowed
     * pending, accepted, rejected, delivering, completed. This adds 'cancelled'.
     *
     * Uses raw SQL because Doctrine DBAL (used by Schema::table enum changes)
     * is not a project dependency; this is the standard way to alter a MySQL
     * enum column without it.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','accepted','rejected','delivering','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','accepted','rejected','delivering','completed') NOT NULL DEFAULT 'pending'");
        }
    }
};
