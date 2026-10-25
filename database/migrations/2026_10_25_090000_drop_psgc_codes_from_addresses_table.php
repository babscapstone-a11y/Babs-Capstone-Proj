<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops addresses.province_code, municipality_code and barangay_code. Each only identified the
 * place its name column already holds, so the name was stored twice. The profile's address
 * dropdowns pre-select by name instead: each list only holds the places inside the one picked
 * above it (a province's cities, a city's barangays), where names are unique.
 * Skipped when already done, so it can be run again.
 */
return new class extends Migration
{
    private const COLUMNS = ['province_code', 'municipality_code', 'barangay_code'];

    public function up(): void
    {
        $columns = array_values(array_filter(self::COLUMNS, fn ($col) => Schema::hasColumn('addresses', $col)));

        if ($columns) {
            Schema::table('addresses', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    /** The codes come back empty (the backup has the old values); saving the profile fills them again */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'province_code')) {
                $table->string('province_code', 10)->nullable()->after('postal_code');
            }
            if (! Schema::hasColumn('addresses', 'municipality_code')) {
                $table->string('municipality_code', 10)->nullable()->after('province_code');
            }
            if (! Schema::hasColumn('addresses', 'barangay_code')) {
                $table->string('barangay_code', 10)->nullable()->after('municipality_code');
            }
        });
    }
};
