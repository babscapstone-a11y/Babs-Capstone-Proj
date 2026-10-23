<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops inventory_items.supplier, portion_size and portion_unit, which the inventory screens no
 * longer use: suppliers aren't recorded, and the portion per serving belongs to each dish
 * (menu_item_rtc.portion_per_serving), since one meat can go into dishes with different portions.
 * Skipped when already done, so it can be run again.
 */
return new class extends Migration
{
    private const COLUMNS = ['supplier', 'portion_size', 'portion_unit'];

    public function up(): void
    {
        $columns = array_values(array_filter(self::COLUMNS, fn ($col) => Schema::hasColumn('inventory_items', $col)));

        if ($columns) {
            Schema::table('inventory_items', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'portion_size')) {
                $table->decimal('portion_size', 10, 4)->nullable();
            }
            if (! Schema::hasColumn('inventory_items', 'portion_unit')) {
                $table->string('portion_unit', 50)->nullable();
            }
            if (! Schema::hasColumn('inventory_items', 'supplier')) {
                $table->string('supplier')->nullable();
            }
        });
    }
};
