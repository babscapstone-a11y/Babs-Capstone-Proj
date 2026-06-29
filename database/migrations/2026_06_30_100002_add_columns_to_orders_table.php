<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number', 30)->unique()->nullable();
            }
            if (! Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('orders', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('orders', 'order_status_id')) {
                $table->foreignId('order_status_id')->nullable()->constrained('order_statuses');
            }
            if (! Schema::hasColumn('orders', 'order_type')) {
                $table->enum('order_type', ['dine_in', 'takeout', 'online'])->default('dine_in');
            }
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            }
            if (! Schema::hasColumn('orders', 'special_instructions')) {
                $table->text('special_instructions')->nullable();
            }
            if (! Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'cancellation_reason')) {
                $table->string('cancellation_reason', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_number', 'total_amount', 'customer_id', 'order_status_id',
                'order_type', 'payment_status', 'special_instructions',
                'cancelled_at', 'cancellation_reason',
            ]);
        });
    }
};
