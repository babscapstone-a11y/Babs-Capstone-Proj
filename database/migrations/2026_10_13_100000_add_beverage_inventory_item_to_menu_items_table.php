<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Picked on Add/Edit Menu Item for a beverage: the stock item one order uses one
            // piece of. A linked beverage is Sold Out when that stock runs out; with no link
            // the beverage isn't stock-tracked.
            $table->foreignId('beverage_inventory_item_id')
                  ->nullable()
                  ->after('rtc_inventory_item_id')
                  ->constrained('inventory_items')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('beverage_inventory_item_id');
        });
    }
};
