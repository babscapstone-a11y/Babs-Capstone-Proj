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
            ['item_name' => 'Pork',          'category' => 'Pork',    'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 5,  'min_stock_level' => 3],
            ['item_name' => 'Pork Belly',    'category' => 'Pork',    'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 5,  'min_stock_level' => 3],
            ['item_name' => 'Liempo',        'category' => 'Pork',    'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 4,  'min_stock_level' => 2],
            ['item_name' => 'Ground Pork',   'category' => 'Pork',    'unit' => 'kg', 'portion_size' => 0.20, 'portion_unit' => 'kg', 'reorder_level' => 3,  'min_stock_level' => 2],
            ['item_name' => 'Beef',          'category' => 'Beef',    'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 5,  'min_stock_level' => 3],
            ['item_name' => 'Chicken',       'category' => 'Chicken', 'unit' => 'pc', 'portion_size' => 1.00, 'portion_unit' => 'pc', 'reorder_level' => 20, 'min_stock_level' => 10],
            ['item_name' => 'Chicken Thigh', 'category' => 'Chicken', 'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 5,  'min_stock_level' => 3],
            ['item_name' => 'Fish',          'category' => 'Fish',    'unit' => 'kg', 'portion_size' => 0.30, 'portion_unit' => 'kg', 'reorder_level' => 4,  'min_stock_level' => 2],
            ['item_name' => 'Bangus',        'category' => 'Fish',    'unit' => 'pc', 'portion_size' => 1.00, 'portion_unit' => 'pc', 'reorder_level' => 10, 'min_stock_level' => 5],
            ['item_name' => 'Shrimp',        'category' => 'Seafood', 'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 3,  'min_stock_level' => 2],
            ['item_name' => 'Squid',         'category' => 'Seafood', 'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 3,  'min_stock_level' => 2],
            ['item_name' => 'Lamb',          'category' => 'Others',  'unit' => 'kg', 'portion_size' => 0.25, 'portion_unit' => 'kg', 'reorder_level' => 3,  'min_stock_level' => 2],
        ];

        foreach ($rtcItems as $item) {
            InventoryItem::firstOrCreate(
                ['item_name' => $item['item_name']],
                array_merge($item, [
                    'item_type'  => 'rtc',
                    'is_rtc'     => true,
                    'quantity'   => 0,
                    'cost_price' => 0,
                    'is_active'  => true,
                ])
            );

            // Update item_type for existing records
            InventoryItem::where('item_name', $item['item_name'])
                ->whereNull('item_type')
                ->orWhere('item_type', '')
                ->update(['item_type' => 'rtc', 'is_rtc' => true]);
        }

        // ── Beverage Items ───────────────────────────────────────────────────
        $beverages = [
            ['item_name' => 'Coca-Cola 1.5L',  'category' => 'Soft Drink', 'unit' => 'bottle', 'reorder_level' => 12, 'min_stock_level' => 6],
            ['item_name' => 'Sprite 1.5L',      'category' => 'Soft Drink', 'unit' => 'bottle', 'reorder_level' => 12, 'min_stock_level' => 6],
            ['item_name' => 'Royal 1.5L',       'category' => 'Soft Drink', 'unit' => 'bottle', 'reorder_level' => 10, 'min_stock_level' => 5],
            ['item_name' => 'Mineral Water',    'category' => 'Water',      'unit' => 'bottle', 'reorder_level' => 24, 'min_stock_level' => 12],
            ['item_name' => 'Iced Tea 1L',      'category' => 'Juice',      'unit' => 'bottle', 'reorder_level' => 12, 'min_stock_level' => 6],
            ['item_name' => 'Mango Juice',      'category' => 'Juice',      'unit' => 'bottle', 'reorder_level' => 10, 'min_stock_level' => 5],
            ['item_name' => 'Red Horse 330ml',  'category' => 'Beer',       'unit' => 'can',    'reorder_level' => 24, 'min_stock_level' => 12],
            ['item_name' => 'San Miguel Light', 'category' => 'Beer',       'unit' => 'can',    'reorder_level' => 24, 'min_stock_level' => 12],
        ];

        foreach ($beverages as $item) {
            InventoryItem::firstOrCreate(
                ['item_name' => $item['item_name']],
                array_merge($item, [
                    'item_type'  => 'beverage',
                    'is_rtc'     => false,
                    'quantity'   => 0,
                    'cost_price' => 0,
                    'is_active'  => true,
                ])
            );
        }
    }
}
