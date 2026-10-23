<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalization of stock_ins:
 * - po_type only repeated the stocked item's inventory_items.item_type (it was copied from the
 *   item on every stock-in), so it's read through inventory_item_id instead.
 * - supplier stopped being entered long ago and nothing reads it.
 *
 * Every step is skipped when already done, so a run that stopped partway can be run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter(['po_type', 'supplier'], fn ($col) => Schema::hasColumn('stock_ins', $col)));

        if ($columns) {
            Schema::table('stock_ins', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_ins', 'po_type')) {
                $table->enum('po_type', ['rtc', 'beverage'])->default('rtc')->after('inventory_item_id');
            }
            if (! Schema::hasColumn('stock_ins', 'supplier')) {
                $table->string('supplier')->nullable()->after('po_type');
            }
        });

        // Each stock-in's type comes back from its item, as it was copied before
        DB::statement('UPDATE stock_ins s JOIN inventory_items i ON i.id = s.inventory_item_id SET s.po_type = i.item_type');
    }
};
