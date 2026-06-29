<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (! Schema::hasColumn('staff', 'first_name')) {
                $table->string('first_name')->after('id');
            }
            if (! Schema::hasColumn('staff', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('staff', 'email')) {
                $table->string('email')->unique()->after('last_name');
            }
            if (! Schema::hasColumn('staff', 'contact_no')) {
                $table->string('contact_no')->nullable()->after('email');
            }
            if (! Schema::hasColumn('staff', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('contact_no')
                      ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $columns = ['user_id', 'contact_no', 'email', 'last_name', 'first_name'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('staff', $col)) {
                    if ($col === 'user_id') {
                        $table->dropForeign(['user_id']);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
