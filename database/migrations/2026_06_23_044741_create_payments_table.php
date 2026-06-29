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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('invoice_id')
              ->constrained('invoices')
              ->cascadeOnDelete();

        $table->foreignId('mode_of_payment_id')
              ->constrained('mode_of_payments');

        $table->decimal('amount_paid', 10, 2);

        $table->string('reference_number')
              ->nullable();

        $table->timestamp('payment_date')
              ->useCurrent();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
