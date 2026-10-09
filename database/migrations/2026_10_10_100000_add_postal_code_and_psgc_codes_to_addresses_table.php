<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Postal code plus the official PSGC codes for the province / city-municipality / barangay
 * picked from the address dropdowns. The names stay in their existing columns for display;
 * the codes let the dropdowns pre-select the saved address reliably.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('postal_code', 4)->nullable()->after('province');
            $table->string('province_code', 10)->nullable()->after('postal_code');
            $table->string('municipality_code', 10)->nullable()->after('province_code');
            $table->string('barangay_code', 10)->nullable()->after('municipality_code');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['postal_code', 'province_code', 'municipality_code', 'barangay_code']);
        });
    }
};
