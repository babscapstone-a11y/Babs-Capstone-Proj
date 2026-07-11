<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'po_number')) {
                $table->dropUnique('purchase_orders_po_number_unique');
                $table->dropColumn('po_number');
            }
            if (Schema::hasColumn('purchase_orders', 'supplier_name')) {
                $table->dropColumn('supplier_name');
            }
            if (Schema::hasColumn('purchase_orders', 'order_date')) {
                $table->dropColumn('order_date');
            }
            if (Schema::hasColumn('purchase_orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'po_number')) {
                $table->string('po_number')->unique()->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'supplier_name')) {
                $table->string('supplier_name')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'order_date')) {
                $table->date('order_date')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0);
            }
        });
    }
};
