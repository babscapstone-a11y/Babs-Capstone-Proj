<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'item_type')) {
                $table->enum('item_type', ['rtc', 'beverage'])->default('rtc')->after('item_name');
            }
            if (! Schema::hasColumn('inventory_items', 'category')) {
                $table->string('category')->nullable()->after('item_type');
            }
            if (! Schema::hasColumn('inventory_items', 'min_stock_level')) {
                $table->decimal('min_stock_level', 10, 4)->default(0)->after('reorder_level');
            }
            if (! Schema::hasColumn('inventory_items', 'rtc_servings')) {
                $table->decimal('rtc_servings', 10, 4)->default(0)->after('min_stock_level');
            }
            if (! Schema::hasColumn('inventory_items', 'portion_size')) {
                $table->decimal('portion_size', 10, 4)->nullable()->after('rtc_servings');
            }
            if (! Schema::hasColumn('inventory_items', 'portion_unit')) {
                $table->string('portion_unit')->nullable()->after('portion_size');
            }
            if (! Schema::hasColumn('inventory_items', 'supplier')) {
                $table->string('supplier')->nullable()->after('portion_unit');
            }
            if (! Schema::hasColumn('inventory_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('supplier');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn([
                'item_type', 'category', 'min_stock_level',
                'rtc_servings', 'portion_size', 'portion_unit', 'supplier', 'is_active',
            ]);
        });
    }
};
