<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The one session this account is signed in on (null = signed in nowhere).
     * One login per account, so it belongs on the account's own row (staff and customers
     * both log in through `users`).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('current_session_id')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('current_session_id');
        });
    }
};
