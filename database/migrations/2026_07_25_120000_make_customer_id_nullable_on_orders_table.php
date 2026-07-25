<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The 2026_06_30_100002 migration only added customer_id as nullable
     * when the column didn't already exist — but create_orders_table had
     * already created it NOT NULL, so that guard silently skipped it and
     * left customer_id NOT NULL on any database that ran migrations in
     * that order. Staff-placed orders (table-server, walk-in) have no
     * customer_id, so inserting one fails with a 23000 integrity error.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });
    }
};
