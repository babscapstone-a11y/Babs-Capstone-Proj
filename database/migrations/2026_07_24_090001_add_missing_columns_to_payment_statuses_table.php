<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_statuses', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_statuses', 'status_name')) {
                $table->string('status_name', 80);
            }
            if (! Schema::hasColumn('payment_statuses', 'description')) {
                $table->string('description', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_statuses', function (Blueprint $table) {
            $table->dropColumn(['status_name', 'description']);
        });
    }
};
