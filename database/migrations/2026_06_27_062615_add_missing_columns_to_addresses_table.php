<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'street')) {
                $table->string('street', 150)->nullable()->after('id');
            }
            if (! Schema::hasColumn('addresses', 'barangay')) {
                $table->string('barangay', 100)->nullable()->after('street');
            }
            if (! Schema::hasColumn('addresses', 'municipality')) {
                $table->string('municipality', 100)->nullable()->after('barangay');
            }
            if (! Schema::hasColumn('addresses', 'province')) {
                $table->string('province', 100)->nullable()->after('municipality');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            foreach (['street', 'barangay', 'municipality', 'province'] as $col) {
                if (Schema::hasColumn('addresses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
