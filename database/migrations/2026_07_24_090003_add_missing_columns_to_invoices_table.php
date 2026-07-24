<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'order_id')) {
                $table->foreignId('order_id')->after('id')
                      ->constrained('orders')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('invoices', 'discount_id')) {
                $table->foreignId('discount_id')->nullable()->after('order_id')
                      ->constrained('discounts')->nullOnDelete();
            }
            if (! Schema::hasColumn('invoices', 'payment_status_id')) {
                $table->foreignId('payment_status_id')->nullable()->after('discount_id')
                      ->constrained('payment_statuses');
            }
            if (! Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('invoices', 'service_charge')) {
                $table->decimal('service_charge', 10, 2)->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('invoices', 'final_total')) {
                $table->decimal('final_total', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_amount', 'service_charge', 'final_total']);

            foreach (['payment_status_id', 'discount_id', 'order_id'] as $fk) {
                if (Schema::hasColumn('invoices', $fk)) {
                    $table->dropForeign([$fk]);
                    $table->dropColumn($fk);
                }
            }
        });
    }
};
