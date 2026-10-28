<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * order_statuses was filled twice on servers where db:seed ran: an earlier migration inserted the
 * statuses, then OrderStatusSeeder added its own copies (including a "Preparing" the system never
 * used). For each status name the lowest id is kept, orders on a duplicate are moved to it, and
 * the duplicates are deleted. "Processing" is renamed "Preparing" (what the screens already call
 * it), so the old seeder's "Preparing" rows merge into it.
 *
 * Every status gets its standard color and order, and status_name becomes unique so duplicates
 * can't come back. On a database that is already clean only the rename and the unique key apply.
 * Undo renames Preparing back to Processing and drops the unique key; deleted duplicates stay deleted.
 */
return new class extends Migration
{
    private const STATUSES = [
        'Pending'   => ['#F59E0B', 1],
        'Preparing' => ['#3B82F6', 2],
        'Ready'     => ['#8B5CF6', 3],
        'Served'    => ['#2563EB', 4],
        'Packaged'  => ['#F59E0B', 5],
        'Completed' => ['#16A34A', 6],
        'Cancelled' => ['#DC2626', 7],
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $groups = DB::table('order_statuses')->orderBy('id')->get()
                ->groupBy(fn ($s) => $s->status_name === 'Processing' ? 'Preparing' : $s->status_name);

            foreach ($groups as $name => $rows) {
                $keep  = $rows->first()->id;
                $extra = $rows->pluck('id')->slice(1)->values()->all();

                if ($extra) {
                    DB::table('orders')->whereIn('order_status_id', $extra)->update(['order_status_id' => $keep]);
                    DB::table('order_statuses')->whereIn('id', $extra)->delete();
                }
                DB::table('order_statuses')->where('id', $keep)->update(['status_name' => $name]);
            }

            $now = now();
            foreach (self::STATUSES as $name => [$color, $sort]) {
                $exists = DB::table('order_statuses')->where('status_name', $name)->exists();
                DB::table('order_statuses')->updateOrInsert(['status_name' => $name],
                    ['color' => $color, 'sort_order' => $sort, 'updated_at' => $now] + ($exists ? [] : ['created_at' => $now]));
            }
        });

        if (! Schema::hasIndex('order_statuses', 'order_statuses_status_name_unique')) {
            Schema::table('order_statuses', fn (Blueprint $table) => $table->unique('status_name'));
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('order_statuses', 'order_statuses_status_name_unique')) {
            Schema::table('order_statuses', fn (Blueprint $table) => $table->dropUnique('order_statuses_status_name_unique'));
        }

        DB::table('order_statuses')->where('status_name', 'Preparing')->update(['status_name' => 'Processing']);
    }
};
