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
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')
                      ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('customers', 'first_name')) {
                $table->string('first_name')->after('user_id');
            }
            if (! Schema::hasColumn('customers', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('customers', 'email')) {
                $table->string('email')->after('last_name');
            }
            if (! Schema::hasColumn('customers', 'contact_no')) {
                $table->string('contact_no', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('customers', 'status')) {
                $table->string('status')->default('active')->after('contact_no');
            }
            if (! Schema::hasColumn('customers', 'address_id')) {
                $table->foreignId('address_id')->nullable()->after('status')
                      ->constrained('addresses')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'address_id')) {
                $table->dropForeign(['address_id']);
                $table->dropColumn('address_id');
            }
            foreach (['status', 'contact_no', 'email', 'last_name', 'first_name'] as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('customers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
