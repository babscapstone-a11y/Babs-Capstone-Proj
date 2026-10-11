<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menu availability is now automatic (MenuItem::availability, from RTC servings).
     * The old manual is_available switch has had no screen to change it since the
     * menu forms dropped it, so any item still set to false was hidden from customers
     * and table servers with no way to turn it back on. Switch them all back on.
     */
    public function up(): void
    {
        DB::table('menu_items')->where('is_available', false)->update(['is_available' => true]);
    }

    public function down(): void
    {
        // Nothing to restore — the previous values were stale.
    }
};
