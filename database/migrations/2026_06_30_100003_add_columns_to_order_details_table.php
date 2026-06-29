<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            if (! Schema::hasColumn('order_details', 'order_id')) {
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('order_details', 'menu_item_id')) {
                $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
            }
            if (! Schema::hasColumn('order_details', 'item_name')) {
                $table->string('item_name', 200);
            }
            if (! Schema::hasColumn('order_details', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1);
            }
            if (! Schema::hasColumn('order_details', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('order_details', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn([
                'order_id', 'menu_item_id', 'item_name', 'quantity', 'price', 'subtotal',
            ]);
        });
    }
};
