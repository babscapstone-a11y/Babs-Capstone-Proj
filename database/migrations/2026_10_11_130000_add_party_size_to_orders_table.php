<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Number of persons for an online Advance Order (customers can book ahead to dine in).
            // Null for same-day Pick-Up and for orders placed by staff.
            $table->unsignedSmallInteger('party_size')->nullable()->after('pickup_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('party_size');
        });
    }
};
