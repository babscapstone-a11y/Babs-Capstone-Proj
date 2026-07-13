<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('conversion_logs', 'menu_item_id')) {
                $table->foreignId('menu_item_id')->nullable()->after('inventory_item_id')
                    ->constrained('menu_items')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            $table->dropForeign(['menu_item_id']);
            $table->dropColumn('menu_item_id');
        });
    }
};
