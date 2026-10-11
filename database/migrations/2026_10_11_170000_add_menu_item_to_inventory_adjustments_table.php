<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adjustments can now also correct a menu item's RTC servings (RTC Units page), not only
     * a stock item's quantity. Each row points at one or the other.
     */
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->foreignId('inventory_item_id')->nullable()->change();
            $table->foreignId('menu_item_id')->nullable()->after('inventory_item_id')
                  ->constrained('menu_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_item_id');
        });
    }
};
