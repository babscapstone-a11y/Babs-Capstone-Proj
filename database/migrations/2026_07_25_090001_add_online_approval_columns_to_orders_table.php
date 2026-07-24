<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'pickup_at')) {
                $table->dateTime('pickup_at')->nullable()->after('special_instructions');
            }
            if (! Schema::hasColumn('orders', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected', 'cancelled'])
                      ->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('approval_status')
                      ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('orders', 'rejection_reason')) {
                $table->string('rejection_reason', 500)->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('orders', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('orders', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            }
            if (Schema::hasColumn('orders', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
            if (Schema::hasColumn('orders', 'pickup_at')) {
                $table->dropColumn('pickup_at');
            }
        });
    }
};
