<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->unique()
                  ->constrained('orders')->cascadeOnDelete();

            $table->foreignId('customer_id')
                  ->constrained('customers')->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['gcash', 'maya', 'bank_transfer', 'other']);
            $table->string('reference_number', 100)->nullable();
            $table->string('proof_image', 255);
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
