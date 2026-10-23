<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * purchase_orders only ever held stock received into inventory (one row per stock-in), while
 * the actual purchase orders live in procurement_orders. Renamed to stock_ins so the names
 * match what each table holds. All rows are kept as they are.
 *
 * MySQL keeps the old names on a renamed table's foreign keys and indexes, so those are
 * renamed too (dropped and re-added, since older MariaDB can't rename an index in place),
 * keeping each key's delete rule. Every step is skipped when already done, so a run that
 * stopped partway can be run again.
 */
return new class extends Migration
{
    /** The table's foreign keys, used to put back any that a stopped run left missing */
    private const KEYS = [
        'inventory_item_id'    => ['inventory_items', 'cascade'],
        'procurement_order_id' => ['procurement_orders', 'set null'],
        'recorded_by'          => ['users', 'set null'],
    ];

    public function up(): void
    {
        if (Schema::hasTable('purchase_orders') && ! Schema::hasTable('stock_ins')) {
            Schema::rename('purchase_orders', 'stock_ins');
        }

        $this->renameKeys('stock_ins', 'purchase_orders_', 'stock_ins_');
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_ins') && ! Schema::hasTable('purchase_orders')) {
            Schema::rename('stock_ins', 'purchase_orders');
        }

        $this->renameKeys('purchase_orders', 'stock_ins_', 'purchase_orders_');
    }

    private function renameKeys(string $table, string $from, string $to): void
    {
        // Foreign keys: drop, drop the index MySQL leaves behind, re-add under the new name
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

        // A key a stopped run dropped but didn't get to re-add
        $keyed = collect(Schema::getForeignKeys($table))->pluck('columns')->flatten()->all();
        foreach (self::KEYS as $column => [$foreignTable, $onDelete]) {
            if (Schema::hasColumn($table, $column) && ! in_array($column, $keyed, true)) {
                Schema::table($table, fn (Blueprint $t) => $t->foreign($column)
                    ->references('id')->on($foreignTable)->onDelete($onDelete));
            }
        }

        // Any other index still carrying the old name
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
