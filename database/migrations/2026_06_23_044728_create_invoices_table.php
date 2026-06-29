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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
              ->constrained('orders')
              ->cascadeOnDelete();

        $table->foreignId('discount_id')
              ->nullable()
              ->constrained('discounts')
              ->nullOnDelete();

        $table->foreignId('payment_status_id')
              ->constrained('payment_statuses');

        $table->decimal('subtotal', 10, 2);

        $table->decimal('discount_amount', 10, 2)
              ->default(0);

        $table->decimal('final_total', 10, 2);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
