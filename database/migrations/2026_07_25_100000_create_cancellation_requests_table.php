<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancellation_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number', 30)->unique()->nullable();

            $table->foreignId('order_id')
                  ->unique()
                  ->constrained('orders')
                  ->cascadeOnDelete();

            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->cascadeOnDelete();

            $table->text('cancellation_reason');

            $table->enum('review_status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('review_date')->nullable();

            $table->string('rejection_reason', 500)->nullable();

            $table->timestamps();

            $table->index(['review_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_requests');
    }
};
