<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtc_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('servings', 10, 4)->default(0);
            $table->decimal('portion_size', 10, 4)->nullable();
            $table->string('portion_unit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtc_products');
    }
};
