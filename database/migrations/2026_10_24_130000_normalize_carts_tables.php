<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalizes the cart: a cart is now only the customer's current, unchecked-out selection.
 * - Completed carts are deleted (their items were already copied into order_details at
 *   checkout); from now on checkout deletes the cart instead of marking it completed.
 * - carts.status is dropped (every cart left is active) and each customer has one cart.
 * - cart_items.unit_price is dropped: it copied menu_items.price, so the cart now always
 *   shows and charges the dish's current price. order_details.price still records what
 *   was paid.
 *
 * Every step is skipped when already done, so a run that stopped partway can be run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('carts', 'status')) {
            // Their items go too (cart_items.cart_id cascades on delete)
            DB::table('carts')->where('status', '!=', 'active')->delete();
            DB::table('carts')->whereNull('customer_id')->delete();

            // One cart per customer: keep each customer's newest active cart
            $keep = DB::table('carts')->selectRaw('MAX(id)')->groupBy('customer_id');
            DB::table('carts')->whereNotIn('id', $keep)->delete();

            Schema::table('carts', fn (Blueprint $table) => $table->dropColumn('status'));
        }

        if (! Schema::hasIndex('carts', 'carts_customer_id_unique')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable(false)->change();
                $table->unique('customer_id');
            });
        }

        if (Schema::hasColumn('cart_items', 'unit_price')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->dropColumn('unit_price'));
        }
    }

    /** Completed carts can't come back (they were copies of order_details); the backup has them */
    public function down(): void
    {
        if (! Schema::hasColumn('cart_items', 'unit_price')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->decimal('unit_price', 10, 2)->default(0)->after('notes'));
            DB::statement('UPDATE cart_items ci JOIN menu_items m ON m.id = ci.menu_item_id SET ci.unit_price = m.price');
        }

        // The customer_id foreign key uses the unique index, so it's dropped and re-added around it
        if (Schema::hasIndex('carts', 'carts_customer_id_unique')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropUnique(['customer_id']);
            });
            Schema::table('carts', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->change();
                $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('carts', 'status')) {
            Schema::table('carts', fn (Blueprint $table) => $table->string('status')->default('active')->after('customer_id'));
        }
    }
};
