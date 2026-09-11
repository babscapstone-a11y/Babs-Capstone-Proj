<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_discount_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number', 30)->unique()->nullable();

            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();

            $table->foreignId('discount_id')
                  ->constrained('discounts')
                  ->cascadeOnDelete();

            $table->foreignId('cashier_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->decimal('requested_amount', 10, 2);

            $table->string('reason', 500)->nullable();

            $table->enum('review_status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('review_date')->nullable();

            $table->string('rejection_reason', 500)->nullable();

            $table->timestamps();

            $table->index(['order_id', 'review_status']);
            $table->index(['review_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_discount_requests');
    }
};
