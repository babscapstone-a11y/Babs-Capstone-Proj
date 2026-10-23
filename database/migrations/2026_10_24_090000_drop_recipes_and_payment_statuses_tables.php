<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes two unused tables:
 * - recipes: never used; menu items link to their ready-to-cook stock through the RTC
 *   columns on menu_items instead.
 * - payment_statuses (with invoices.payment_status_id): every invoice was saved as "Paid"
 *   and nothing ever read it back. An order's payment status is orders.payment_status.
 *
 * Every step is skipped when already done, so a run that stopped partway can be run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Stop before changing anything if recipes holds data (it could only have been added by hand)
        if (Schema::hasTable('recipes') && ($rows = DB::table('recipes')->count()) > 0) {
            throw new RuntimeException("The recipes table has {$rows} row(s). It is unused by the system, but check "
                . 'whether that data is needed (export it from phpMyAdmin if so), then empty the table and run the migration again.');
        }

        if (Schema::hasColumn('invoices', 'payment_status_id')) {
            $hasKey = collect(Schema::getForeignKeys('invoices'))
                ->contains(fn ($fk) => $fk['columns'] === ['payment_status_id']);

            Schema::table('invoices', function (Blueprint $table) use ($hasKey) {
                if ($hasKey) {
                    $table->dropForeign(['payment_status_id']);
                }
                $table->dropColumn('payment_status_id');
            });
        }

        Schema::dropIfExists('payment_statuses');
        Schema::dropIfExists('recipes');
    }

    public function down(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity_required', 10, 2);
            $table->timestamps();
        });

        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status_name', 80);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        $now = now();
        foreach (['Pending', 'Paid', 'Failed', 'Refunded'] as $status) {
            DB::table('payment_statuses')->insert(['status_name' => $status, 'created_at' => $now, 'updated_at' => $now]);
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('payment_status_id')->nullable()->after('discount_id')->constrained('payment_statuses');
        });

        // Every invoice is created when the order is paid
        DB::table('invoices')->update([
            'payment_status_id' => DB::table('payment_statuses')->where('status_name', 'Paid')->value('id'),
        ]);
    }
};
