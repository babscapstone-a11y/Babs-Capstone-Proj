<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalization: only online orders are ever cancelled with a date and reason (the
 * automatic "not picked up by closing time" cancellation), so those fields move
 * from `orders` into the `online_orders` subtype table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('rejection_reason');
            $table->string('cancellation_reason', 500)->nullable()->after('cancelled_at');
        });

        DB::statement("
            UPDATE online_orders oo
            JOIN orders o ON o.id = oo.order_id
            SET oo.cancelled_at = o.cancelled_at, oo.cancellation_reason = o.cancellation_reason
        ");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancellation_reason']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable()->after('cancelled_at');
        });

        DB::statement("
            UPDATE orders o
            JOIN online_orders oo ON oo.order_id = o.id
            SET o.cancelled_at = oo.cancelled_at, o.cancellation_reason = oo.cancellation_reason
        ");

        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancellation_reason']);
        });
    }
};
