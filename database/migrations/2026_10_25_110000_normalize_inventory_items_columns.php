<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cleans up inventory_items:
 * - pack_unit / pieces_per_pack only applied to beverages bought by the Case/Box, so every other
 *   row held NULLs: they move to inventory_item_packs, one row per item that comes in a pack
 * - category was free text left empty on every raw meat; it is dropped, together with the copy
 *   procurement_order_items took of it
 * - is_rtc repeated item_type (is_rtc = 1 exactly when item_type = 'rtc')
 * - min_stock_level was a second threshold beside reorder_level, which is the one that drives
 *   low stock, restocking, reports and purchase orders
 *
 * Every step checks first, so a half-finished run can simply be run again.
 * Undo restores the columns and the packs, but not the dropped category text (see the backup).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_item_packs')) {
            Schema::create('inventory_item_packs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_item_id')->unique()->constrained('inventory_items')->cascadeOnDelete();
                $table->string('pack_unit', 20);                     // Case or Box
                $table->unsignedSmallInteger('pieces_per_pack');     // pieces (bottles/cans) in one pack
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('inventory_items', 'pack_unit')) {
            DB::table('inventory_item_packs')->insertUsing(
                ['inventory_item_id', 'pack_unit', 'pieces_per_pack', 'created_at', 'updated_at'],
                DB::table('inventory_items as i')
                    ->select('i.id', 'i.pack_unit', 'i.pieces_per_pack', DB::raw('NOW()'), DB::raw('NOW()'))
                    ->whereNotNull('i.pack_unit')
                    ->where('i.pieces_per_pack', '>', 1)
                    ->whereNotExists(fn (Builder $q) => $q->from('inventory_item_packs as p')->whereColumn('p.inventory_item_id', 'i.id')),
            );
        }

        foreach (['pack_unit', 'pieces_per_pack', 'category', 'is_rtc', 'min_stock_level'] as $column) {
            if (Schema::hasColumn('inventory_items', $column)) {
                Schema::table('inventory_items', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }

        if (Schema::hasColumn('procurement_order_items', 'category')) {
            Schema::table('procurement_order_items', fn (Blueprint $table) => $table->dropColumn('category'));
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('procurement_order_items', 'category')) {
            Schema::table('procurement_order_items', fn (Blueprint $table) => $table->string('category', 100)->nullable()->after('item_name'));
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'category')) {
                $table->string('category')->nullable()->after('item_type');
            }
            if (! Schema::hasColumn('inventory_items', 'pack_unit')) {
                $table->string('pack_unit', 20)->nullable()->after('unit');
            }
            if (! Schema::hasColumn('inventory_items', 'pieces_per_pack')) {
                $table->unsignedSmallInteger('pieces_per_pack')->nullable()->after('pack_unit');
            }
            if (! Schema::hasColumn('inventory_items', 'min_stock_level')) {
                $table->decimal('min_stock_level', 10, 4)->default(0)->after('reorder_level');
            }
            if (! Schema::hasColumn('inventory_items', 'is_rtc')) {
                $table->boolean('is_rtc')->default(false)->after('cost_price');
            }
        });

        DB::table('inventory_items')->update([
            'is_rtc'          => DB::raw("item_type = 'rtc'"),
            'min_stock_level' => DB::raw('reorder_level'),
        ]);

        if (Schema::hasTable('inventory_item_packs')) {
            foreach (DB::table('inventory_item_packs')->get() as $pack) {
                DB::table('inventory_items')->where('id', $pack->inventory_item_id)
                    ->update(['pack_unit' => $pack->pack_unit, 'pieces_per_pack' => $pack->pieces_per_pack]);
            }
        }

        Schema::dropIfExists('inventory_item_packs');
    }
};
