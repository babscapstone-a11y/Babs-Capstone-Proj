<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->nullOnDelete();
            $table->string('item_name');
            $table->string('category', 100)->nullable();
            $table->string('item_type', 50)->default('rtc');
            $table->decimal('current_stock', 10, 4)->default(0);
            $table->decimal('threshold', 10, 4)->default(0);
            $table->decimal('quantity_recommended', 10, 4)->default(0);
            $table->decimal('quantity_to_purchase', 10, 4);
            $table->string('unit', 50);
            $table->string('stock_status', 50)->default('low_stock');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_order_items');
    }
};
