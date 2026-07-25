<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Stamped by the Kitchen (Processing -> Ready) so the food-server
            // board can show "waiting since ready" separately from total
            // order age, which also includes kitchen prep time.
            $table->timestamp('ready_at')->nullable()->after('order_status_id');
            $table->foreignId('served_by')->nullable()->after('placed_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('served_at')->nullable()->after('served_by');
            $table->timestamp('packaged_at')->nullable()->after('served_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['served_by']);
            $table->dropColumn(['ready_at', 'served_by', 'served_at', 'packaged_at']);
        });
    }
};
