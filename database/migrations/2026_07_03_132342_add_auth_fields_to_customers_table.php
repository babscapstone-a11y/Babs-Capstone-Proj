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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->string('status')->default('active')->after('contact_no');
            $table->rememberToken()->after('status');
        });

        // Preserve the real password hash / status of customers that today
        // only authenticate via their linked `users` row. (MySQL-only join-update
        // syntax; the SQLite test database always starts empty, so there's
        // nothing to backfill there.)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'UPDATE customers
                 INNER JOIN users ON users.id = customers.user_id
                 SET customers.password = users.password, customers.status = users.status
                 WHERE customers.user_id IS NOT NULL'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['password', 'status', 'remember_token']);
        });
    }
};
