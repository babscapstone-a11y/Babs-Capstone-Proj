<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets every part of an address be left blank (e.g. no street / house number).
 *
 * create_addresses_table made street, barangay, municipality and province NOT NULL. The later
 * add_missing_columns_to_addresses_table meant to make them nullable, but it only adds columns
 * that don't exist yet — on a freshly migrated database (like the live server) they already
 * existed, so they stayed NOT NULL and saving a profile with a blank street failed with a 500.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street', 150)->nullable()->change();
            $table->string('barangay', 100)->nullable()->change();
            $table->string('municipality', 100)->nullable()->change();
            $table->string('province', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Left nullable on purpose: forcing NOT NULL back would fail on any address saved
        // with a blank part, and the app has treated these as optional all along.
    }
};
