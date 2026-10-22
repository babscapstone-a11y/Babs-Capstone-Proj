<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff get a system-made default password (last name + birthday), so their birthday is recorded
        Schema::table('staff', function (Blueprint $table) {
            $table->date('birthday')->nullable()->after('last_name');
        });

        // Set while an account is still on its default password: the first log-in asks for a new one
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('birthday');
        });
    }
};
