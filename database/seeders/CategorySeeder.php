<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Appetizers',       'description' => 'Starters and small bites'],
            ['category_name' => 'Main Course',       'description' => 'Hearty main dishes'],
            ['category_name' => 'Rice & Noodles',   'description' => 'Rice and noodle-based dishes'],
            ['category_name' => 'Seafood',           'description' => 'Fresh seafood dishes'],
            ['category_name' => 'Soups & Salads',   'description' => 'Light soups and fresh salads'],
            ['category_name' => 'Grilled & BBQ',    'description' => 'Grilled and barbecued dishes'],
            ['category_name' => 'Snacks & Sides',   'description' => 'Side dishes and snacks'],
            ['category_name' => 'Desserts',          'description' => 'Sweet treats and desserts'],
            ['category_name' => 'Beverages',         'description' => 'Drinks and refreshments'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['category_name' => $cat['category_name']], $cat);
        }
    }
}
