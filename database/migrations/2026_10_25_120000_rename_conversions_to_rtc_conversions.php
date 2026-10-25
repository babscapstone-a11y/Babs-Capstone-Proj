<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames conversions → rtc_conversions and conversion_lines → rtc_conversion_details (header /
 * details, like orders / order_details), and the link column conversion_id → rtc_conversion_id.
 *
 * MariaDB keeps foreign key and index names when a table is renamed, so each foreign key is
 * dropped and added again under the new table's name. Every step checks first, so a
 * half-finished run can simply be run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->renameTable('conversions', 'rtc_conversions');
        $this->renameTable('conversion_lines', 'rtc_conversion_details');

        $this->relink('rtc_conversions', 'menu_item_id', 'menu_items', 'set null');
        $this->relink('rtc_conversions', 'converted_by', 'users', 'set null');
        $this->relink('rtc_conversion_details', 'rtc_conversion_id', 'rtc_conversions', 'cascade', 'conversion_id');
        $this->relink('rtc_conversion_details', 'inventory_item_id', 'inventory_items', 'cascade');
    }

    public function down(): void
    {
        $this->renameTable('rtc_conversions', 'conversions');
        $this->renameTable('rtc_conversion_details', 'conversion_lines');

        $this->relink('conversions', 'menu_item_id', 'menu_items', 'set null');
        $this->relink('conversions', 'converted_by', 'users', 'set null');
        $this->relink('conversion_lines', 'conversion_id', 'conversions', 'cascade', 'rtc_conversion_id');
        $this->relink('conversion_lines', 'inventory_item_id', 'inventory_items', 'cascade');
    }

    private function renameTable(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }

    /**
     * Gives $table.$column a foreign key named after $table (renaming the column from $oldColumn
     * first, if given), replacing the key that still carries the old table's name.
     */
    private function relink(string $table, string $column, string $foreignTable, string $onDelete, ?string $oldColumn = null): void
    {
        $name = "{$table}_{$column}_foreign";

        foreach (Schema::getForeignKeys($table) as $fk) {
            if ($fk['name'] !== $name && in_array($fk['columns'], [[$column], [$oldColumn ?? $column]], true)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropForeign($fk['name']));
                if (Schema::hasIndex($table, $fk['name'])) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($fk['name']));
                }
            }
        }

        if ($oldColumn && Schema::hasColumn($table, $oldColumn) && ! Schema::hasColumn($table, $column)) {
            Schema::table($table, fn (Blueprint $t) => $t->renameColumn($oldColumn, $column));
        }

        if (! collect(Schema::getForeignKeys($table))->contains('name', $name)) {
            Schema::table($table, fn (Blueprint $t) => $t->foreign($column, $name)->references('id')->on($foreignTable)->onDelete($onDelete));
        }
    }
};
