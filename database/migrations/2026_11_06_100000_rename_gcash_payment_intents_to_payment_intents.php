<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames gcash_payment_intents → payment_intents.
 *
 * MariaDB keeps key names when a table is renamed, so the foreign keys and the unique key on
 * paymongo_payment_intent_id are dropped and added again under the new name. Every step checks
 * first, so a half-finished run can simply be run again.
 */
return new class extends Migration
{
    /** column => [referenced table, on delete] */
    private const FOREIGN_KEYS = [
        'order_id'    => ['orders', 'cascade'],
        'cashier_id'  => ['users', 'cascade'],
        'discount_id' => ['discounts', 'set null'],
        'payment_id'  => ['payments', 'set null'],
    ];

    public function up(): void
    {
        $this->rename('gcash_payment_intents', 'payment_intents');
    }

    public function down(): void
    {
        $this->rename('payment_intents', 'gcash_payment_intents');
    }

    private function rename(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }

        // Foreign keys still named after the old table: drop them and their indexes
        foreach (Schema::getForeignKeys($to) as $fk) {
            if (! str_starts_with($fk['name'], "{$to}_")) {
                Schema::table($to, fn (Blueprint $t) => $t->dropForeign($fk['name']));
                if (Schema::hasIndex($to, $fk['name'])) {
                    Schema::table($to, fn (Blueprint $t) => $t->dropIndex($fk['name']));
                }
            }
        }

        $unique = "{$to}_paymongo_payment_intent_id_unique";
        foreach (Schema::getIndexes($to) as $index) {
            if ($index['columns'] === ['paymongo_payment_intent_id'] && $index['name'] !== $unique) {
                Schema::table($to, fn (Blueprint $t) => $t->dropUnique($index['name']));
            }
        }
        if (! Schema::hasIndex($to, $unique)) {
            Schema::table($to, fn (Blueprint $t) => $t->unique('paymongo_payment_intent_id', $unique));
        }

        $existing = collect(Schema::getForeignKeys($to))->pluck('name');
        foreach (self::FOREIGN_KEYS as $column => [$table, $onDelete]) {
            $name = "{$to}_{$column}_foreign";
            if (! $existing->contains($name)) {
                Schema::table($to, fn (Blueprint $t) => $t->foreign($column, $name)->references('id')->on($table)->onDelete($onDelete));
            }
        }
    }
};
