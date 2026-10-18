<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A follow-up PO for the short / not-bought items of an earlier PO points back to it
        Schema::table('procurement_orders', function (Blueprint $table) {
            $table->foreignId('source_po_id')->nullable()->after('prepared_by')->constrained('procurement_orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('procurement_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_po_id');
        });
    }
};
