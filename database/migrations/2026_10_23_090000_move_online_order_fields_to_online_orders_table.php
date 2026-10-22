<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalization: fields that only apply to online orders (pick-up schedule and the
 * cashier approval) move out of `orders` into the `online_orders` subtype table,
 * the same way `table_number` lives in `dine_in_orders`. The unused delivery
 * columns are dropped (online orders are pick-up only).
 */
return new class extends Migration
{
    private const MOVED = ['pickup_at', 'party_size', 'approval_status', 'reviewed_by', 'reviewed_at', 'rejection_reason'];

    /**
     * Every step is skipped when already done, so a run that stopped partway (MySQL can't roll
     * back table changes) can simply be run again.
     */
    public function up(): void
    {
        // The delivery columns are dropped first: on databases where they're NOT NULL
        // (as first created), copying rows in below would fail while they still exist
        $delivery = array_values(array_filter(['delivery_address', 'contact_number'],
            fn ($col) => Schema::hasColumn('online_orders', $col)));
        if ($delivery) {
            Schema::table('online_orders', fn (Blueprint $table) => $table->dropColumn($delivery));
        }

        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'pickup_at')) {
                $table->dateTime('pickup_at')->nullable()->after('order_id');
            }
            if (! Schema::hasColumn('online_orders', 'party_size')) {
                $table->unsignedSmallInteger('party_size')->nullable()->after('pickup_at');
            }
            if (! Schema::hasColumn('online_orders', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected', 'cancelled'])
                      ->default('pending')->after('party_size');
            }
            if (! Schema::hasColumn('online_orders', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('approval_status')
                      ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('online_orders', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('online_orders', 'rejection_reason')) {
                $table->string('rejection_reason', 500)->nullable()->after('reviewed_at');
            }

            // One online_orders row per order (one-to-one with orders)
            if (! Schema::hasIndex('online_orders', 'online_orders_order_id_unique')) {
                $table->unique('order_id');
            }
        });

        // Copy every existing online order's data into its new subtype row
        if (Schema::hasColumn('orders', 'approval_status')) DB::statement("
            INSERT INTO online_orders
                (order_id, pickup_at, party_size, approval_status, reviewed_by, reviewed_at, rejection_reason, created_at, updated_at)
            SELECT o.id, o.pickup_at, o.party_size, COALESCE(o.approval_status, 'pending'),
                   o.reviewed_by, o.reviewed_at, o.rejection_reason, o.created_at, o.updated_at
            FROM orders o
            WHERE o.order_type = 'online'
              AND NOT EXISTS (SELECT 1 FROM online_orders oo WHERE oo.order_id = o.id)
        ");

        $hasReviewerKey = collect(Schema::getForeignKeys('orders'))
            ->contains(fn ($fk) => $fk['columns'] === ['reviewed_by']);
        $moved = array_values(array_filter(self::MOVED, fn ($col) => Schema::hasColumn('orders', $col)));

        Schema::table('orders', function (Blueprint $table) use ($hasReviewerKey, $moved) {
            if ($hasReviewerKey) {
                $table->dropForeign(['reviewed_by']);
            }
            if ($moved) {
                $table->dropColumn($moved);
            }
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->string('delivery_address')->nullable()->after('order_id');
            $table->string('contact_number')->nullable()->after('delivery_address');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'cancelled'])
                  ->nullable()->after('payment_method');
            $table->foreignId('reviewed_by')->nullable()->after('approval_status')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->string('rejection_reason', 500)->nullable()->after('reviewed_at');
            $table->dateTime('pickup_at')->nullable()->after('special_instructions');
            $table->unsignedSmallInteger('party_size')->nullable()->after('pickup_at');
        });

        DB::statement("
            UPDATE orders o
            JOIN online_orders oo ON oo.order_id = o.id
            SET o.pickup_at = oo.pickup_at, o.party_size = oo.party_size,
                o.approval_status = oo.approval_status, o.reviewed_by = oo.reviewed_by,
                o.reviewed_at = oo.reviewed_at, o.rejection_reason = oo.rejection_reason
        ");

        // MySQL/MariaDB reuse the unique index for the order_id foreign key, so the
        // key is dropped first and re-added once the plain index is back
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropUnique(['order_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(self::MOVED);
        });

        Schema::table('online_orders', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        // The online_orders rows were empty before this migration
        DB::table('online_orders')->delete();
    }
};
