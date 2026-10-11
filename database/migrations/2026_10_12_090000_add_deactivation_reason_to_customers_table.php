<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Why an admin deactivated the account; shown to the customer when they try to log in.
            // Both are cleared again when the account is reactivated.
            $table->string('deactivation_reason', 255)->nullable()->after('status');
            $table->timestamp('deactivated_at')->nullable()->after('deactivation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivated_at']);
        });
    }
};
