<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'inventory_item_id')) {
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('purchase_orders', 'po_type')) {
                $table->enum('po_type', ['rtc', 'beverage'])->default('rtc');
            }
            if (! Schema::hasColumn('purchase_orders', 'supplier')) {
                $table->string('supplier')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'quantity_purchased')) {
                $table->decimal('quantity_purchased', 10, 4);
            }
            if (! Schema::hasColumn('purchase_orders', 'unit')) {
                $table->string('unit', 50);
            }
            if (! Schema::hasColumn('purchase_orders', 'previous_quantity')) {
                $table->decimal('previous_quantity', 10, 4)->default(0);
            }
            if (! Schema::hasColumn('purchase_orders', 'new_quantity')) {
                $table->decimal('new_quantity', 10, 4)->default(0);
            }
            if (! Schema::hasColumn('purchase_orders', 'purchase_date')) {
                $table->date('purchase_date');
            }
            if (! Schema::hasColumn('purchase_orders', 'remarks')) {
                $table->text('remarks')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'recorded_by')) {
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropColumn([
                'inventory_item_id', 'po_type', 'supplier', 'quantity_purchased',
                'unit', 'previous_quantity', 'new_quantity', 'purchase_date',
                'remarks', 'recorded_by',
            ]);
        });
    }
};
