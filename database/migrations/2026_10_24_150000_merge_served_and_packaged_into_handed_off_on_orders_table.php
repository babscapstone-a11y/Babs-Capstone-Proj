<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One hand-off per order instead of two half-empty columns:
 * - served_at (dine-in) and packaged_at (take-out / online) were never both set, so one was
 *   always NULL and which one was set only repeated the order's service type. Merged into
 *   handed_off_at; whether it was served or packaged follows from the service type.
 * - served_by also recorded whoever packaged an order, so it's renamed handed_off_by.
 *
 * New columns are added and filled before the old ones are dropped, so every step can be
 * skipped when already done and a run that stopped partway can be run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'handed_off_by')) {
                $table->foreignId('handed_off_by')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'handed_off_at')) {
                $table->timestamp('handed_off_at')->nullable()->after('ready_at');
            }
        });

        if (Schema::hasColumn('orders', 'served_at')) {
            DB::statement('UPDATE orders SET handed_off_at = COALESCE(served_at, packaged_at) WHERE handed_off_at IS NULL');
        }
        if (Schema::hasColumn('orders', 'served_by')) {
            DB::statement('UPDATE orders SET handed_off_by = served_by WHERE handed_off_by IS NULL');

            $hasKey = collect(Schema::getForeignKeys('orders'))->contains(fn ($fk) => $fk['columns'] === ['served_by']);
            Schema::table('orders', function (Blueprint $table) use ($hasKey) {
                if ($hasKey) {
                    $table->dropForeign(['served_by']);
                }
                $table->dropColumn('served_by');
            });
        }

        $old = array_values(array_filter(['served_at', 'packaged_at'], fn ($col) => Schema::hasColumn('orders', $col)));
        if ($old) {
            Schema::table('orders', fn (Blueprint $table) => $table->dropColumn($old));
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'served_by')) {
                $table->foreignId('served_by')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'served_at')) {
                $table->timestamp('served_at')->nullable()->after('served_by');
            }
            if (! Schema::hasColumn('orders', 'packaged_at')) {
                $table->timestamp('packaged_at')->nullable()->after('served_at');
            }
        });

        if (Schema::hasColumn('orders', 'handed_off_at')) {
            // Dine-in orders were served; take-out and online orders were packaged
            DB::statement("
                UPDATE orders o LEFT JOIN walk_in_orders w ON w.order_id = o.id
                SET o.served_at   = IF(w.service_type = 'dine_in', o.handed_off_at, NULL),
                    o.packaged_at = IF(w.service_type = 'dine_in', NULL, o.handed_off_at),
                    o.served_by   = o.handed_off_by
            ");

            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['handed_off_by']);
                $table->dropColumn(['handed_off_by', 'handed_off_at']);
            });
        }
    }
};
