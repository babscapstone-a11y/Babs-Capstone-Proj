<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Draft → Finalized → Stocked In (SQLite, used by the test suite, doesn't support MODIFY)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE procurement_orders MODIFY status ENUM('draft','finalized','stocked_in') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('procurement_orders', function (Blueprint $table) {
            $table->timestamp('stocked_in_at')->nullable()->after('finalized_at');
            $table->foreignId('stocked_in_by')->nullable()->after('stocked_in_at')->constrained('users')->nullOnDelete();
        });

        // What the owner actually bought, entered after the shopping trip
        Schema::table('procurement_order_items', function (Blueprint $table) {
            $table->decimal('quantity_received', 10, 4)->nullable()->after('quantity_to_purchase');
            $table->decimal('amount_paid', 12, 2)->nullable()->after('quantity_received');
        });

        // Link each stock-in record back to the purchase order that created it (null = manual stock-in)
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('procurement_order_id')->nullable()->after('id')->constrained('procurement_orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('procurement_order_id');
        });

        Schema::table('procurement_order_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_received', 'amount_paid']);
        });

        Schema::table('procurement_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stocked_in_by');
            $table->dropColumn('stocked_in_at');
        });

        DB::table('procurement_orders')->where('status', 'stocked_in')->update(['status' => 'finalized']);
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE procurement_orders MODIFY status ENUM('draft','finalized') NOT NULL DEFAULT 'draft'");
        }
    }
};
