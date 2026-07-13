<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('conversion_logs', 'rtc_product_id')) {
            Schema::table('conversion_logs', function (Blueprint $table) {
                $table->dropForeign(['rtc_product_id']);
                $table->dropColumn('rtc_product_id');
            });
        }

        Schema::dropIfExists('rtc_products');
    }

    public function down(): void
    {
        Schema::create('rtc_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('servings', 10, 4)->default(0);
            $table->decimal('portion_size', 10, 4)->nullable();
            $table->string('portion_unit')->nullable();
            $table->timestamps();
        });

        Schema::table('conversion_logs', function (Blueprint $table) {
            $table->foreignId('rtc_product_id')->nullable()->after('inventory_item_id')
                ->constrained('rtc_products')->nullOnDelete();
        });
    }
};
