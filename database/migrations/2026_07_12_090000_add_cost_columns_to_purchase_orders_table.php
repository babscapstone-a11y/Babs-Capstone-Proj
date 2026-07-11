<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('unit');
            }
            if (! Schema::hasColumn('purchase_orders', 'total_cost')) {
                $table->decimal('total_cost', 10, 2)->nullable()->after('unit_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'total_cost']);
        });
    }
};
