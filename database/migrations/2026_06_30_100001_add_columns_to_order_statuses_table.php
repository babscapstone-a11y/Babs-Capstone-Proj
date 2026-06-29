<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_statuses', function (Blueprint $table) {
            if (! Schema::hasColumn('order_statuses', 'status_name')) {
                $table->string('status_name', 80);
            }
            if (! Schema::hasColumn('order_statuses', 'color')) {
                $table->string('color', 30)->default('#6B7280');
            }
            if (! Schema::hasColumn('order_statuses', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0);
            }
        });

        // Seed default statuses
        DB::table('order_statuses')->insertOrIgnore([
            ['id' => 1, 'status_name' => 'Pending',    'color' => '#F59E0B', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'status_name' => 'Processing', 'color' => '#3B82F6', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'status_name' => 'Ready',      'color' => '#8B5CF6', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'status_name' => 'Completed',  'color' => '#16A34A', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'status_name' => 'Cancelled',  'color' => '#DC2626', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->dropColumn(['status_name', 'color', 'sort_order']);
        });
    }
};
