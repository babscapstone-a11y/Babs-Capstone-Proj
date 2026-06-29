<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            if (! Schema::hasColumn('discounts', 'discount_name')) {
                $table->string('discount_name', 150)->unique();
            }
            if (! Schema::hasColumn('discounts', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            }
            if (! Schema::hasColumn('discounts', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('discounts', 'eligibility_type')) {
                $table->enum('eligibility_type', [
                    'senior_citizen', 'pwd', 'promotional', 'employee',
                    'minimum_purchase', 'date_range', 'all_customers',
                ])->default('all_customers');
            }
            if (! Schema::hasColumn('discounts', 'minimum_purchase')) {
                $table->decimal('minimum_purchase', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('discounts', 'maximum_discount')) {
                $table->decimal('maximum_discount', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('discounts', 'start_date')) {
                $table->date('start_date')->nullable();
            }
            if (! Schema::hasColumn('discounts', 'end_date')) {
                $table->date('end_date')->nullable();
            }
            if (! Schema::hasColumn('discounts', 'description')) {
                $table->text('description')->nullable();
            }
            if (! Schema::hasColumn('discounts', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn([
                'discount_name', 'discount_type', 'discount_value', 'eligibility_type',
                'minimum_purchase', 'maximum_discount',
                'start_date', 'end_date', 'description', 'status',
            ]);
        });
    }
};
