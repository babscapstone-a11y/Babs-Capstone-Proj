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
        Schema::table('menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_items', 'menu_name')) {
                $table->string('menu_name')->after('id');
            }
            if (! Schema::hasColumn('menu_items', 'item_type')) {
                $table->enum('item_type', ['food', 'beverage'])->default('food')->after('menu_name');
            }
            if (! Schema::hasColumn('menu_items', 'description')) {
                $table->text('description')->nullable()->after('item_type');
            }
            if (! Schema::hasColumn('menu_items', 'price')) {
                $table->decimal('price', 10, 2)->after('description');
            }
            if (! Schema::hasColumn('menu_items', 'image')) {
                $table->string('image')->nullable()->after('price');
            }
            if (! Schema::hasColumn('menu_items', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('image');
            }
            if (! Schema::hasColumn('menu_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_available');
            }
            if (! Schema::hasColumn('menu_items', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('is_active')
                      ->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('menu_items', 'rtc_inventory_item_id')) {
                $table->foreignId('rtc_inventory_item_id')->nullable()->after('category_id')
                      ->constrained('inventory_items')->nullOnDelete();
            }
            if (! Schema::hasColumn('menu_items', 'rtc_quantity')) {
                $table->decimal('rtc_quantity', 10, 4)->nullable()->after('rtc_inventory_item_id');
            }
            if (! Schema::hasColumn('menu_items', 'rtc_unit')) {
                $table->string('rtc_unit')->nullable()->after('rtc_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $fks = ['rtc_inventory_item_id', 'category_id'];
            foreach ($fks as $fk) {
                if (Schema::hasColumn('menu_items', $fk)) {
                    $table->dropForeign(['menu_items_' . $fk . '_foreign']);
                    $table->dropColumn($fk);
                }
            }
            $cols = ['menu_name','item_type','description','price','image','is_available','is_active','rtc_quantity','rtc_unit'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('menu_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
