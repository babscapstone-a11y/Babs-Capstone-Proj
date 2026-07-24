<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mode_of_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('mode_of_payments', 'method_name')) {
                $table->string('method_name', 80);
            }
        });
    }

    public function down(): void
    {
        Schema::table('mode_of_payments', function (Blueprint $table) {
            $table->dropColumn(['method_name']);
        });
    }
};
