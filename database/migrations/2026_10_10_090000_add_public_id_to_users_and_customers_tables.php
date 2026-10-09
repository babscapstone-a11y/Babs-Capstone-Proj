<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Random public ids for staff (users) and customers so their URLs don't expose
 * sequential database ids (/customers/5 → /customers/01k7c9q2...).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'customers'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->char('public_id', 26)->nullable()->unique()->after('id');
            });

            // Give every existing account its own public id
            DB::table($table)->whereNull('public_id')->orderBy('id')->pluck('id')->each(function ($id) use ($table) {
                DB::table($table)->where('id', $id)->update(['public_id' => strtolower((string) Str::ulid())]);
            });
        }
    }

    public function down(): void
    {
        foreach (['users', 'customers'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropUnique("{$table}_public_id_unique");
                $t->dropColumn('public_id');
            });
        }
    }
};
