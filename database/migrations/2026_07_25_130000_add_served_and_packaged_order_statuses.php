<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Module 20 (Order Completion & Service Fulfillment) inserts two new
     * statuses between Ready and Completed: Served (dine-in, food handed to
     * the customer) and Packaged (take-out/online, boxed and ready for
     * pickup). Existing sort_order values for Completed/Cancelled are
     * bumped up to make room so the lookup table stays in workflow order.
     */
    public function up(): void
    {
        DB::table('order_statuses')->where('status_name', 'Completed')->update(['sort_order' => 6]);
        DB::table('order_statuses')->where('status_name', 'Cancelled')->update(['sort_order' => 7]);

        DB::table('order_statuses')->updateOrInsert(
            ['status_name' => 'Served'],
            ['color' => '#2563EB', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('order_statuses')->updateOrInsert(
            ['status_name' => 'Packaged'],
            ['color' => '#F59E0B', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('order_statuses')->whereIn('status_name', ['Served', 'Packaged'])->delete();
        DB::table('order_statuses')->where('status_name', 'Completed')->update(['sort_order' => 4]);
        DB::table('order_statuses')->where('status_name', 'Cancelled')->update(['sort_order' => 5]);
    }
};
