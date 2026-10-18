<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A dish made from several raw meats is one conversion with one log row per meat;
        // the rows of one conversion share a batch code (null = older single-meat conversions)
        Schema::table('conversion_logs', function (Blueprint $table) {
            $table->string('batch_code', 36)->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            $table->dropIndex(['batch_code']);
            $table->dropColumn('batch_code');
        });
    }
};
