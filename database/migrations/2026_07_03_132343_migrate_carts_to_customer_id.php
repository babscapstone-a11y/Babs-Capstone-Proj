<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')
                  ->constrained('customers')->cascadeOnDelete();
        });

        // MySQL-only join-update syntax; the SQLite test database always starts
        // empty, so there's nothing to backfill there.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'UPDATE carts
                 INNER JOIN customers ON customers.user_id = carts.user_id
                 SET carts.customer_id = customers.id'
            );
        }

        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained('users')->cascadeOnDelete();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
