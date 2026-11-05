<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replaces the single "Beverages" menu category with two beverage categories:
 * "Soft Drinks" and "Juice".
 *
 * The drinks are moved first because menu_items.category_id cascades on delete —
 * deleting "Beverages" while drinks still point at it would delete the drinks too.
 * Coca Cola goes to Soft Drinks; every other drink (Iced Tea, Calamansi Juice, …)
 * goes to Juice. Admins can re-file any item later from the Menu Catalog.
 */
return new class extends Migration
{
    private const SOFT_DRINKS = ['Coca Cola'];

    public function up(): void
    {
        DB::transaction(function () {
            $softDrinksId = $this->category('Soft Drinks', 'Carbonated drinks and sodas');
            $juiceId      = $this->category('Juice', 'Juices, iced tea and other non-carbonated drinks');

            $beverages = DB::table('categories')->where('category_name', 'Beverages')->first();
            if (! $beverages) {
                return;
            }

            DB::table('menu_items')->where('category_id', $beverages->id)
                ->whereIn('menu_name', self::SOFT_DRINKS)
                ->update(['category_id' => $softDrinksId]);

            DB::table('menu_items')->where('category_id', $beverages->id)
                ->update(['category_id' => $juiceId]);

            DB::table('categories')->where('id', $beverages->id)->delete();
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $beveragesId = $this->category('Beverages', 'Drinks and refreshments');

            $newIds = DB::table('categories')->whereIn('category_name', ['Soft Drinks', 'Juice'])->pluck('id');
            DB::table('menu_items')->whereIn('category_id', $newIds)->update(['category_id' => $beveragesId]);
            DB::table('categories')->whereIn('id', $newIds)->delete();
        });
    }

    /** Finds the beverage category by name, creating it if needed; returns its id. */
    private function category(string $name, string $description): int
    {
        $existing = DB::table('categories')->where('category_name', $name)->value('id');
        if ($existing) {
            DB::table('categories')->where('id', $existing)->update(['item_type' => 'beverage', 'is_active' => true]);
            return $existing;
        }

        return DB::table('categories')->insertGetId([
            'category_name' => $name,
            'item_type'     => 'beverage',
            'description'   => $description,
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
};
