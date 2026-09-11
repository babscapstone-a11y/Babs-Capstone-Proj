<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the 'special' discount type — a cashier-requested, admin-approved
     * custom amount — alongside the existing 'percentage'/'fixed' rules.
     * MySQL enums can't be altered through the schema builder, so this runs
     * a raw MODIFY COLUMN statement.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE discounts MODIFY discount_type ENUM('percentage', 'fixed', 'special') NOT NULL DEFAULT 'percentage'");
    }

    public function down(): void
    {
        DB::statement("UPDATE discounts SET discount_type = 'fixed' WHERE discount_type = 'special'");
        DB::statement("ALTER TABLE discounts MODIFY discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage'");
    }
};
