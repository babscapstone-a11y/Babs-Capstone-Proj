<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'invoice_id')) {
                $table->foreignId('invoice_id')->after('id')
                      ->constrained('invoices')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('payments', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('invoice_id')
                      ->constrained('orders')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('payments', 'cashier_id')) {
                $table->foreignId('cashier_id')->nullable()->after('order_id')
                      ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'mode_of_payment_id')) {
                $table->foreignId('mode_of_payment_id')->nullable()->after('cashier_id')
                      ->constrained('mode_of_payments');
            }
            if (! Schema::hasColumn('payments', 'transaction_number')) {
                $table->string('transaction_number', 40)->nullable()->unique()->after('mode_of_payment_id');
            }
            if (! Schema::hasColumn('payments', 'receipt_number')) {
                $table->string('receipt_number', 40)->nullable()->unique()->after('transaction_number');
            }
            if (! Schema::hasColumn('payments', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('payments', 'amount_received')) {
                $table->decimal('amount_received', 10, 2)->default(0)->after('amount_paid');
            }
            if (! Schema::hasColumn('payments', 'change_amount')) {
                $table->decimal('change_amount', 10, 2)->default(0)->after('amount_received');
            }
            if (! Schema::hasColumn('payments', 'reference_number')) {
                $table->string('reference_number', 100)->nullable();
            }
            if (! Schema::hasColumn('payments', 'payment_date')) {
                $table->timestamp('payment_date')->useCurrent();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'amount_paid', 'amount_received', 'change_amount',
                'reference_number', 'transaction_number', 'receipt_number', 'payment_date',
            ]);

            foreach (['mode_of_payment_id', 'cashier_id', 'order_id', 'invoice_id'] as $fk) {
                if (Schema::hasColumn('payments', $fk)) {
                    $table->dropForeign([$fk]);
                    $table->dropColumn($fk);
                }
            }
        });
    }
};
