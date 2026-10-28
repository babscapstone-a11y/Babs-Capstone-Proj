<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When the kitchen marked this ordered item as done (null = still cooking).
     * Belongs to the item itself, so it sits on order_details.
     */
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->timestamp('prepared_at')->nullable()->after('rtc_deducted_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('prepared_at');
        });
    }
};
