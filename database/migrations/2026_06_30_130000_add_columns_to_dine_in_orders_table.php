<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dine_in_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('dine_in_orders', 'order_id')) {
                $table->foreignId('order_id')->after('id')
                      ->constrained('orders')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('dine_in_orders', 'table_number')) {
                $table->integer('table_number')->nullable()->after('order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dine_in_orders', function (Blueprint $table) {
            if (Schema::hasColumn('dine_in_orders', 'table_number')) {
                $table->dropColumn('table_number');
            }
            if (Schema::hasColumn('dine_in_orders', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
        });
    }
};
