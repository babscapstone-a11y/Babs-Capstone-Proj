<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two-level order type:
 * - orders.order_type is now the channel: "walk_in" (taken by a food server) or "online".
 * - dine_in_orders becomes walk_in_orders, the walk-in subtype: service_type ("dine_in" /
 *   "takeout"), table_number (dine-in only) and placed_by (the staff member who took it,
 *   moved from orders, where it was empty for every online order).
 * Every walk-in order now has a walk_in_orders row, take-out included, just as every online
 * order has an online_orders row.
 *
 * Every step is skipped when already done, so a run that stopped partway can be run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dine_in_orders') && ! Schema::hasTable('walk_in_orders')) {
            Schema::rename('dine_in_orders', 'walk_in_orders');
        }
        $this->renameKeys('walk_in_orders', 'dine_in_orders_', 'walk_in_orders_');

        Schema::table('walk_in_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('walk_in_orders', 'service_type')) {
                $table->enum('service_type', ['dine_in', 'takeout'])->default('dine_in')->after('order_id');
            }
            if (! Schema::hasColumn('walk_in_orders', 'placed_by')) {
                $table->foreignId('placed_by')->nullable()->after('table_number')->constrained('users')->nullOnDelete();
            }
        });

        // While orders still says dine_in / takeout: every walk-in order gets its row (take-out
        // orders had none), with the staff member who took it
        if ($this->orderTypeValuesInclude('takeout')) {
            DB::statement("
                INSERT INTO walk_in_orders (order_id, service_type, table_number, created_at, updated_at)
                SELECT o.id, o.order_type, NULL, o.created_at, o.updated_at
                FROM orders o
                WHERE o.order_type IN ('dine_in', 'takeout')
                  AND NOT EXISTS (SELECT 1 FROM walk_in_orders w WHERE w.order_id = o.id)
            ");
            DB::statement("
                UPDATE walk_in_orders w JOIN orders o ON o.id = w.order_id
                SET w.service_type = o.order_type
                WHERE o.order_type IN ('dine_in', 'takeout')
            ");
        }

        if (Schema::hasColumn('orders', 'placed_by')) {
            DB::statement('UPDATE walk_in_orders w JOIN orders o ON o.id = w.order_id SET w.placed_by = o.placed_by');
        }

        // One walk_in_orders row per order
        if (! Schema::hasIndex('walk_in_orders', 'walk_in_orders_order_id_unique')) {
            Schema::table('walk_in_orders', fn (Blueprint $table) => $table->unique('order_id'));
        }

        // orders.order_type: dine_in / takeout → walk_in
        DB::statement("ALTER TABLE orders MODIFY order_type ENUM('dine_in','takeout','walk_in','online') NOT NULL DEFAULT 'walk_in'");
        DB::table('orders')->whereIn('order_type', ['dine_in', 'takeout'])->update(['order_type' => 'walk_in']);
        DB::statement("ALTER TABLE orders MODIFY order_type ENUM('walk_in','online') NOT NULL DEFAULT 'walk_in'");

        if (Schema::hasColumn('orders', 'placed_by')) {
            $hasKey = collect(Schema::getForeignKeys('orders'))->contains(fn ($fk) => $fk['columns'] === ['placed_by']);
            Schema::table('orders', function (Blueprint $table) use ($hasKey) {
                if ($hasKey) {
                    $table->dropForeign(['placed_by']);
                }
                $table->dropColumn('placed_by');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'placed_by')) {
            Schema::table('orders', fn (Blueprint $table) => $table->foreignId('placed_by')->nullable()->after('customer_id')
                ->constrained('users')->nullOnDelete());
        }
        DB::statement('UPDATE orders o JOIN walk_in_orders w ON w.order_id = o.id SET o.placed_by = w.placed_by');

        DB::statement("ALTER TABLE orders MODIFY order_type ENUM('dine_in','takeout','walk_in','online') NOT NULL DEFAULT 'dine_in'");
        DB::statement("UPDATE orders o JOIN walk_in_orders w ON w.order_id = o.id SET o.order_type = w.service_type WHERE o.order_type = 'walk_in'");
        DB::table('orders')->where('order_type', 'walk_in')->update(['order_type' => 'dine_in']);
        DB::statement("ALTER TABLE orders MODIFY order_type ENUM('dine_in','takeout','online') NOT NULL DEFAULT 'dine_in'");

        // Take-out orders had no row before
        DB::table('walk_in_orders')->where('service_type', 'takeout')->delete();

        // The order_id foreign key uses the unique index, so it's dropped and re-added around it
        if (Schema::hasIndex('walk_in_orders', 'walk_in_orders_order_id_unique')) {
            Schema::table('walk_in_orders', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->dropUnique(['order_id']);
            });
            Schema::table('walk_in_orders', fn (Blueprint $table) => $table->foreign('order_id')
                ->references('id')->on('orders')->cascadeOnDelete());
        }

        Schema::table('walk_in_orders', function (Blueprint $table) {
            if (Schema::hasColumn('walk_in_orders', 'placed_by')) {
                $table->dropForeign(['placed_by']);
                $table->dropColumn('placed_by');
            }
            if (Schema::hasColumn('walk_in_orders', 'service_type')) {
                $table->dropColumn('service_type');
            }
        });

        if (Schema::hasTable('walk_in_orders') && ! Schema::hasTable('dine_in_orders')) {
            Schema::rename('walk_in_orders', 'dine_in_orders');
        }
        $this->renameKeys('dine_in_orders', 'walk_in_orders_', 'dine_in_orders_');
    }

    private function orderTypeValuesInclude(string $value): bool
    {
        $type = DB::selectOne("SHOW COLUMNS FROM orders LIKE 'order_type'")->Type ?? '';

        return str_contains($type, "'{$value}'");
    }

    /**
     * MySQL keeps the old names on a renamed table's foreign keys and indexes; renames them
     * (drop and re-add, since older MariaDB can't rename an index in place), keeping each key's rules.
     */
    private function renameKeys(string $table, string $from, string $to): void
    {
        foreach (Schema::getForeignKeys($table) as $fk) {
            if (! str_starts_with($fk['name'], $from)) {
                continue;
            }

            Schema::table($table, fn (Blueprint $t) => $t->dropForeign($fk['name']));

            if (Schema::hasIndex($table, $fk['name'])) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($fk['name']));
            }

            Schema::table($table, fn (Blueprint $t) => $t->foreign($fk['columns'])
                ->references($fk['foreign_columns'])->on($fk['foreign_table'])
                ->onDelete($fk['on_delete'])->onUpdate($fk['on_update']));
        }

        // The order key a stopped run dropped but didn't get to re-add
        $keyed = collect(Schema::getForeignKeys($table))->pluck('columns')->flatten()->all();
        if (! in_array('order_id', $keyed, true)) {
            Schema::table($table, fn (Blueprint $t) => $t->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete());
        }

        foreach (Schema::getIndexes($table) as $index) {
            if ($index['primary'] || ! str_starts_with($index['name'], $from)) {
                continue;
            }

            $newName = $to . substr($index['name'], strlen($from));

            Schema::table($table, function (Blueprint $t) use ($index, $newName) {
                $t->dropIndex($index['name']);
                $index['unique'] ? $t->unique($index['columns'], $newName) : $t->index($index['columns'], $newName);
            });
        }
    }
};
