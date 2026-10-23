<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * roles.display_name only held a readable form of role_name ("kitchen_staff" → "Kitchen Staff");
 * the label is now made from role_name itself (Role::getLabelAttribute).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', fn (Blueprint $table) => $table->dropColumn('display_name'));
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', fn (Blueprint $table) => $table->string('display_name')->nullable());
        }

        $labels = [
            'admin' => 'Administrator', 'cashier' => 'Cashier', 'kitchen_staff' => 'Kitchen Staff',
            'table_server' => 'Table Server', 'customer' => 'Customer',
        ];
        foreach ($labels as $roleName => $label) {
            DB::table('roles')->where('role_name', $roleName)->update(['display_name' => $label]);
        }
    }
};
