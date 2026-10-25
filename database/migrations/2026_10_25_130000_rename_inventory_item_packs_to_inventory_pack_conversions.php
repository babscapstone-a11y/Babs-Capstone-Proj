<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames inventory_item_packs → inventory_pack_conversions (how many pieces one Case/Box of an
 * item converts to).
 *
 * MariaDB keeps key names when a table is renamed, so the foreign key and the unique key on
 * inventory_item_id are dropped and added again under the new name (the foreign key first, as
 * it uses the unique key). Every step checks first, so a half-finished run can simply be run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->rename('inventory_item_packs', 'inventory_pack_conversions');
    }

    public function down(): void
    {
        $this->rename('inventory_pack_conversions', 'inventory_item_packs');
    }

    private function rename(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }

        $fk     = "{$to}_inventory_item_id_foreign";
        $unique = "{$to}_inventory_item_id_unique";

        foreach (Schema::getForeignKeys($to) as $key) {
            if ($key['name'] !== $fk) {
                Schema::table($to, fn (Blueprint $t) => $t->dropForeign($key['name']));
            }
        }
        foreach (Schema::getIndexes($to) as $index) {
            if ($index['columns'] === ['inventory_item_id'] && $index['name'] !== $unique && $index['name'] !== $fk) {
                Schema::table($to, fn (Blueprint $t) => $t->dropIndex($index['name']));
            }
        }

        if (! Schema::hasIndex($to, $unique)) {
            Schema::table($to, fn (Blueprint $t) => $t->unique('inventory_item_id', $unique));
        }
        if (! collect(Schema::getForeignKeys($to))->contains('name', $fk)) {
            Schema::table($to, fn (Blueprint $t) => $t->foreign('inventory_item_id', $fk)->references('id')->on('inventory_items')->cascadeOnDelete());
        }
    }
};
