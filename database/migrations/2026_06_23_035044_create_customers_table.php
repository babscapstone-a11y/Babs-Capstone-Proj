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
    Schema::create('customers', function (Blueprint $table) {
        $table->id();

        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('contact_no', 15);
        $table->string('status')->default('active');
        $table->foreignId('address_id')
              ->nullable()
              ->constrained('addresses')
              ->nullOnDelete();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
