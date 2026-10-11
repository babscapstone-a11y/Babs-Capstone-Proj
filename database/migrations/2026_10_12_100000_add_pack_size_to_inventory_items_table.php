<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Beverages are counted per piece (bottle/can) but usually arrive by the Case or Box, and
     * the pack size differs per item (1 L Coke = 6 per Case, 8 oz Coke = 12). Storing the pack
     * and its size per item lets Stock-In accept "1 Case" and add the right number of pieces.
     * Both null = only ever received by the piece.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('pack_unit', 20)->nullable()->after('unit');
            $table->unsignedSmallInteger('pieces_per_pack')->nullable()->after('pack_unit');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['pack_unit', 'pieces_per_pack']);
        });
    }
};
