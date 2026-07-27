<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Raw statement to avoid a doctrine/dbal dependency just for a
        // nullable toggle on an existing column.
        DB::statement('ALTER TABLE payment_proofs MODIFY proof_image VARCHAR(255) NULL');

        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->enum('payment_type', ['half', 'full'])->default('full')->after('amount');
            $table->string('paymongo_payment_intent_id', 100)->nullable()->unique()->after('proof_image');
            $table->string('paymongo_payment_method_id', 100)->nullable()->after('paymongo_payment_intent_id');
            $table->text('paymongo_checkout_url')->nullable()->after('paymongo_payment_method_id');
            $table->enum('status', ['awaiting_payment', 'paid', 'failed'])->default('awaiting_payment')->after('paymongo_checkout_url');
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'paymongo_payment_intent_id', 'paymongo_payment_method_id', 'paymongo_checkout_url', 'status']);
        });

        DB::statement('ALTER TABLE payment_proofs MODIFY proof_image VARCHAR(255) NOT NULL');
    }
};
