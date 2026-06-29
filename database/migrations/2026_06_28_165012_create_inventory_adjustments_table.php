<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->enum('adjustment_type', ['damaged', 'expired', 'missing', 'correction']);
            $table->decimal('quantity_before', 10, 4);
            $table->decimal('quantity_adjusted', 10, 4); // negative = deduction, positive = addition
            $table->decimal('quantity_after', 10, 4);
            $table->string('reason');
            $table->text('remarks')->nullable();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
