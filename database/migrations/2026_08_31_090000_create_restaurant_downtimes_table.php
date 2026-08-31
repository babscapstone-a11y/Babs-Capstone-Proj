<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_downtimes', function (Blueprint $table) {
            $table->id();

            // dateTime, not timestamp: MySQL only allows one NOT-NULL timestamp
            // column per table without an explicit default before it rejects
            // the implicit zero-date default under strict mode.
            $table->dateTime('starts_at');

            // Authoritative end of the downtime window — either the time the
            // admin originally scheduled, or moved up to now() if ended early.
            // A downtime is "currently active" whenever ends_at is in the future.
            $table->dateTime('ends_at');

            $table->string('reason', 255)->nullable();

            $table->foreignId('set_by_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Only set when an admin manually resumed service before the
            // scheduled ends_at — kept for the audit trail, not used to
            // determine whether the downtime is currently active.
            $table->dateTime('ended_early_at')->nullable();
            $table->foreignId('ended_by_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_downtimes');
    }
};
