<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE customers MODIFY contact_no VARCHAR(20) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE customers SET contact_no = '' WHERE contact_no IS NULL");
        DB::statement('ALTER TABLE customers MODIFY contact_no VARCHAR(15) NOT NULL');
    }
};
