<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used by the test suite) doesn't support MySQL's MODIFY syntax
        // and factories always supply contact_no, so this is a MySQL-only fixup.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE customers MODIFY contact_no VARCHAR(20) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("UPDATE customers SET contact_no = '' WHERE contact_no IS NULL");
            DB::statement('ALTER TABLE customers MODIFY contact_no VARCHAR(15) NOT NULL');
        }
    }
};
