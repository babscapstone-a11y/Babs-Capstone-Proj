<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Minutes the kitchen has manually tacked onto the base prep
            // estimate (see Order::getEstimatedCompletionAttribute) — lets
            // staff push back a customer's expected ready time when a ticket
            // is running long, without touching created_at.
            $table->unsignedInteger('extra_prep_minutes')->default(0)->after('special_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('extra_prep_minutes');
        });
    }
};
