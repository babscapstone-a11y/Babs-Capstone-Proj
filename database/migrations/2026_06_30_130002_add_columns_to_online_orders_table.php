<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'order_id')) {
                $table->foreignId('order_id')->after('id')
                      ->constrained('orders')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('online_orders', 'delivery_address')) {
                $table->string('delivery_address')->nullable()->after('order_id');
            }
            if (! Schema::hasColumn('online_orders', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('delivery_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            foreach (['contact_number', 'delivery_address'] as $col) {
                if (Schema::hasColumn('online_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('online_orders', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
        });
    }
};
