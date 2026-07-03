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
        $customerRoleId = DB::table('roles')->where('role_name', 'customer')->value('id');

        if ($customerRoleId) {
            // Hard-delete (bypassing SoftDeletes) the users rows that only
            // ever existed to represent a customer login.
            DB::table('users')->where('role_id', $customerRoleId)->delete();
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        if ($customerRoleId) {
            DB::table('roles')->where('id', $customerRoleId)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained('users')->nullOnDelete();
        });
    }
};
