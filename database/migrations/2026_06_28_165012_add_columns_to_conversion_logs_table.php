<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('conversion_logs', 'inventory_item_id')) {
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('conversion_logs', 'raw_quantity_used')) {
                $table->decimal('raw_quantity_used', 10, 4);
            }
            if (! Schema::hasColumn('conversion_logs', 'unit')) {
                $table->string('unit', 50);
            }
            if (! Schema::hasColumn('conversion_logs', 'portion_size')) {
                $table->decimal('portion_size', 10, 4);
            }
            if (! Schema::hasColumn('conversion_logs', 'rtc_units_produced')) {
                $table->decimal('rtc_units_produced', 10, 4);
            }
            if (! Schema::hasColumn('conversion_logs', 'previous_raw_stock')) {
                $table->decimal('previous_raw_stock', 10, 4)->default(0);
            }
            if (! Schema::hasColumn('conversion_logs', 'remaining_raw_stock')) {
                $table->decimal('remaining_raw_stock', 10, 4)->default(0);
            }
            if (! Schema::hasColumn('conversion_logs', 'previous_rtc_servings')) {
                $table->decimal('previous_rtc_servings', 10, 4)->default(0);
            }
            if (! Schema::hasColumn('conversion_logs', 'new_rtc_servings')) {
                $table->decimal('new_rtc_servings', 10, 4)->default(0);
            }
            if (! Schema::hasColumn('conversion_logs', 'converted_by')) {
                $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('conversion_logs', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->dropForeign(['converted_by']);
            $table->dropColumn([
                'inventory_item_id', 'raw_quantity_used', 'unit', 'portion_size',
                'rtc_units_produced', 'previous_raw_stock', 'remaining_raw_stock',
                'previous_rtc_servings', 'new_rtc_servings', 'converted_by', 'remarks',
            ]);
        });
    }
};
