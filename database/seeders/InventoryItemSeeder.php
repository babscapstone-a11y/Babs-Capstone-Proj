<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        // ── RTC (Ready-to-Cook) Raw Meat Items ──────────────────────────────
        $rtcItems = [
            ['item_name' => 'Pork',          'unit' => 'kg', 'reorder_level' => 5],
            ['item_name' => 'Pork Belly',    'unit' => 'kg', 'reorder_level' => 5],
            ['item_name' => 'Liempo',        'unit' => 'kg', 'reorder_level' => 4],
            ['item_name' => 'Ground Pork',   'unit' => 'kg', 'reorder_level' => 3],
            ['item_name' => 'Beef',          'unit' => 'kg', 'reorder_level' => 5],
            ['item_name' => 'Chicken',       'unit' => 'pc', 'reorder_level' => 20],
            ['item_name' => 'Chicken Thigh', 'unit' => 'kg', 'reorder_level' => 5],
            ['item_name' => 'Fish',          'unit' => 'kg', 'reorder_level' => 4],
            ['item_name' => 'Bangus',        'unit' => 'pc', 'reorder_level' => 10],
            ['item_name' => 'Shrimp',        'unit' => 'kg', 'reorder_level' => 3],
            ['item_name' => 'Squid',         'unit' => 'kg', 'reorder_level' => 3],
            ['item_name' => 'Lamb',          'unit' => 'kg', 'reorder_level' => 3],
        ];

        foreach ($rtcItems as $item) {
            InventoryItem::firstOrCreate(
                ['item_name' => $item['item_name']],
                array_merge($item, [
                    'item_type'  => 'rtc',
                    'quantity'   => 0,
                    'cost_price' => 0,
                    'is_active'  => true,
                ])
            );
        }

        // ── Beverage Items ───────────────────────────────────────────────────
        $beverages = [
            ['item_name' => 'Coca-Cola 1.5L',   'unit' => 'bottle', 'reorder_level' => 12],
            ['item_name' => 'Sprite 1.5L',      'unit' => 'bottle', 'reorder_level' => 12],
            ['item_name' => 'Royal 1.5L',       'unit' => 'bottle', 'reorder_level' => 10],
            ['item_name' => 'Mineral Water',    'unit' => 'bottle', 'reorder_level' => 24],
            ['item_name' => 'Iced Tea 1L',      'unit' => 'bottle', 'reorder_level' => 12],
            ['item_name' => 'Mango Juice',      'unit' => 'bottle', 'reorder_level' => 10],
            ['item_name' => 'Red Horse 330ml',  'unit' => 'can',    'reorder_level' => 24],
            ['item_name' => 'San Miguel Light', 'unit' => 'can',    'reorder_level' => 24],
        ];

        foreach ($beverages as $item) {
            InventoryItem::firstOrCreate(
                ['item_name' => $item['item_name']],
                array_merge($item, [
                    'item_type'  => 'beverage',
                    'quantity'   => 0,
                    'cost_price' => 0,
                    'is_active'  => true,
                ])
            );
        }
    }
}
