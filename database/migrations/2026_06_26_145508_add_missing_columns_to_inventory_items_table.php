<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'item_name')) {
                $table->string('item_name')->after('id');
            }
            if (! Schema::hasColumn('inventory_items', 'unit')) {
                $table->string('unit')->after('item_name');
            }
            if (! Schema::hasColumn('inventory_items', 'quantity')) {
                $table->decimal('quantity', 10, 2)->default(0)->after('unit');
            }
            if (! Schema::hasColumn('inventory_items', 'reorder_level')) {
                $table->decimal('reorder_level', 10, 2)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('inventory_items', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->default(0)->after('reorder_level');
            }
            if (! Schema::hasColumn('inventory_items', 'is_rtc')) {
                $table->boolean('is_rtc')->default(false)->after('cost_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            foreach (['item_name', 'unit', 'quantity', 'reorder_level', 'cost_price', 'is_rtc'] as $col) {
                if (Schema::hasColumn('inventory_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
