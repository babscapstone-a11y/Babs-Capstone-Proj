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
    Schema::create('conversion_logs', function (Blueprint $table) {
        $table->id();

        $table->foreignId('inventory_item_id')
              ->constrained('inventory_items');

        $table->string('action');

        $table->decimal('quantity_before', 10, 2);

        $table->decimal('quantity_after', 10, 2);

        $table->text('remarks')
              ->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_logs');
    }
};
