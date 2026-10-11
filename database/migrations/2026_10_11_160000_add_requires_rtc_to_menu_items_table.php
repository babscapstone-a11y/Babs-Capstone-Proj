<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Checked on Add/Edit Menu Item for dishes made from converted raw meat: their
            // servings are counted and they're Sold Out until RTC stock is converted for them.
            $table->boolean('requires_rtc')->default(false)->after('rtc_unit');
        });

        // Dishes already set up through an RTC conversion keep being tracked
        DB::table('menu_items')->where('item_type', 'food')->whereNotNull('rtc_quantity')->update(['requires_rtc' => true]);
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('requires_rtc');
        });
    }
};
