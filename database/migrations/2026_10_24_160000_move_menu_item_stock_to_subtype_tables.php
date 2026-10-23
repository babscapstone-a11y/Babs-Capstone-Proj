<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalizes menu stock tracking. menu_items held columns that each applied to one kind of dish:
 * - RTC-tracked food → menu_item_rtc: the raw meat it's made from, the portion per serving, and
 *   the ready-to-cook servings in stock. A row existing replaces requires_rtc. The portion is kept
 *   in the unit the Conversion screen uses (grams for meat stocked in kilograms, otherwise the
 *   meat's own unit), so rtc_unit is no longer stored; older portions saved in kilograms are
 *   converted to grams (0.45 kg → 450 g).
 * - Stock-linked beverages → menu_item_beverages: the stock item one order uses one piece of.
 * - menu_items.item_type repeated categories.item_type, so it's read from the category.
 * Also drops inventory_items.rtc_servings, left over from when servings were kept per raw meat
 * (nothing updates it since servings moved to the dish).
 *
 * Every step is skipped when already done, so a run that stopped partway can be run again.
 */
return new class extends Migration
{
    private const MENU_COLUMNS = ['item_type', 'requires_rtc', 'rtc_inventory_item_id', 'beverage_inventory_item_id',
        'rtc_quantity', 'rtc_unit', 'rtc_servings'];

    public function up(): void
    {
        // Stop before changing anything if a dish's type differs from its category's
        if (Schema::hasColumn('menu_items', 'item_type')) {
            $mismatched = DB::table('menu_items as m')->join('categories as c', 'c.id', '=', 'm.category_id')
                ->whereColumn('m.item_type', '!=', 'c.item_type')->pluck('m.menu_name');

            if ($mismatched->isNotEmpty()) {
                throw new RuntimeException('These menu items have a different type (food/beverage) from their category; '
                    . 'fix their category first, then run the migration again: ' . $mismatched->implode(', '));
            }
        }

        if (! Schema::hasTable('menu_item_rtc')) {
            Schema::create('menu_item_rtc', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_item_id')->unique()->constrained('menu_items')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
                $table->decimal('portion_per_serving', 10, 4)->nullable();
                $table->decimal('servings', 10, 4)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('menu_item_beverages')) {
            Schema::create('menu_item_beverages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_item_id')->unique()->constrained('menu_items')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('menu_items', 'requires_rtc')) {
            DB::statement("
                INSERT INTO menu_item_rtc (menu_item_id, inventory_item_id, portion_per_serving, servings, created_at, updated_at)
                SELECT m.id, m.rtc_inventory_item_id,
                       CASE WHEN m.rtc_unit = 'Kilogram' AND i.unit = 'Kilogram' THEN m.rtc_quantity * 1000 ELSE m.rtc_quantity END,
                       COALESCE(m.rtc_servings, 0), m.created_at, m.updated_at
                FROM menu_items m
                LEFT JOIN inventory_items i ON i.id = m.rtc_inventory_item_id
                WHERE m.item_type = 'food' AND m.requires_rtc = 1
                  AND NOT EXISTS (SELECT 1 FROM menu_item_rtc r WHERE r.menu_item_id = m.id)
            ");
        }

        if (Schema::hasColumn('menu_items', 'beverage_inventory_item_id')) {
            DB::statement("
                INSERT INTO menu_item_beverages (menu_item_id, inventory_item_id, created_at, updated_at)
                SELECT m.id, m.beverage_inventory_item_id, m.created_at, m.updated_at
                FROM menu_items m
                WHERE m.item_type = 'beverage' AND m.beverage_inventory_item_id IS NOT NULL
                  AND NOT EXISTS (SELECT 1 FROM menu_item_beverages b WHERE b.menu_item_id = m.id)
            ");
        }

        $keyed = collect(Schema::getForeignKeys('menu_items'))->pluck('columns')->flatten()->all();
        foreach (['rtc_inventory_item_id', 'beverage_inventory_item_id'] as $column) {
            if (in_array($column, $keyed, true)) {
                Schema::table('menu_items', fn (Blueprint $table) => $table->dropForeign([$column]));
            }
        }

        $columns = array_values(array_filter(self::MENU_COLUMNS, fn ($col) => Schema::hasColumn('menu_items', $col)));
        if ($columns) {
            Schema::table('menu_items', fn (Blueprint $table) => $table->dropColumn($columns));
        }

        if (Schema::hasColumn('inventory_items', 'rtc_servings')) {
            Schema::table('inventory_items', fn (Blueprint $table) => $table->dropColumn('rtc_servings'));
        }
    }

    /** inventory_items.rtc_servings comes back as 0 (its old values were stale; the backup has them) */
    public function down(): void
    {
        if (! Schema::hasColumn('inventory_items', 'rtc_servings')) {
            Schema::table('inventory_items', fn (Blueprint $table) => $table->decimal('rtc_servings', 10, 4)->default(0)->after('cost_price'));
        }

        Schema::table('menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_items', 'item_type')) {
                $table->enum('item_type', ['food', 'beverage'])->default('food')->after('menu_name');
            }
            if (! Schema::hasColumn('menu_items', 'rtc_inventory_item_id')) {
                $table->foreignId('rtc_inventory_item_id')->nullable()->after('category_id')->constrained('inventory_items')->nullOnDelete();
            }
            if (! Schema::hasColumn('menu_items', 'beverage_inventory_item_id')) {
                $table->foreignId('beverage_inventory_item_id')->nullable()->after('rtc_inventory_item_id')->constrained('inventory_items')->nullOnDelete();
            }
            if (! Schema::hasColumn('menu_items', 'rtc_quantity')) {
                $table->decimal('rtc_quantity', 10, 4)->nullable()->after('beverage_inventory_item_id');
            }
            if (! Schema::hasColumn('menu_items', 'rtc_unit')) {
                $table->string('rtc_unit')->nullable()->after('rtc_quantity');
            }
            if (! Schema::hasColumn('menu_items', 'requires_rtc')) {
                $table->boolean('requires_rtc')->default(false)->after('rtc_unit');
            }
            if (! Schema::hasColumn('menu_items', 'rtc_servings')) {
                $table->decimal('rtc_servings', 10, 4)->default(0)->after('requires_rtc');
            }
        });

        DB::statement('UPDATE menu_items m JOIN categories c ON c.id = m.category_id SET m.item_type = c.item_type');

        if (Schema::hasTable('menu_item_rtc')) {
            DB::statement("
                UPDATE menu_items m
                JOIN menu_item_rtc r ON r.menu_item_id = m.id
                LEFT JOIN inventory_items i ON i.id = r.inventory_item_id
                SET m.requires_rtc = 1, m.rtc_inventory_item_id = r.inventory_item_id, m.rtc_quantity = r.portion_per_serving,
                    m.rtc_unit = CASE WHEN i.unit = 'Kilogram' THEN 'Gram' ELSE i.unit END, m.rtc_servings = r.servings
            ");
        }
        if (Schema::hasTable('menu_item_beverages')) {
            DB::statement('UPDATE menu_items m JOIN menu_item_beverages b ON b.menu_item_id = m.id SET m.beverage_inventory_item_id = b.inventory_item_id');
        }

        Schema::dropIfExists('menu_item_beverages');
        Schema::dropIfExists('menu_item_rtc');
    }
};
