<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'item_type')) {
                $table->enum('item_type', ['food', 'beverage'])->default('food')->after('category_name');
            }
        });

        DB::table('categories')->where('category_name', 'Beverages')->update(['item_type' => 'beverage']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });
    }
};
