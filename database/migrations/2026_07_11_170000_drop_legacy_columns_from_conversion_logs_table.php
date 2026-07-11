<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            if (Schema::hasColumn('conversion_logs', 'action')) {
                $table->dropColumn('action');
            }
            if (Schema::hasColumn('conversion_logs', 'quantity_before')) {
                $table->dropColumn('quantity_before');
            }
            if (Schema::hasColumn('conversion_logs', 'quantity_after')) {
                $table->dropColumn('quantity_after');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('conversion_logs', 'action')) {
                $table->string('action')->nullable();
            }
            if (! Schema::hasColumn('conversion_logs', 'quantity_before')) {
                $table->decimal('quantity_before', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('conversion_logs', 'quantity_after')) {
                $table->decimal('quantity_after', 10, 2)->nullable();
            }
        });
    }
};
